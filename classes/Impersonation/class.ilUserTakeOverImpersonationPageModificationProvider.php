<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Layout\Page\Standard;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Factory;
use ILIAS\Data\URI;

/**
 * This modification provider adds a mode info component to the current page during an
 * active impersonation, if no other ILIAS mode is active.
 *
 * Since ILIAS offers oter modes which can be started while an impersonation is active,
 * they are treated as nested. In such cases, this provider must not return its own mode
 * info component, since another one already provides one. See conflicting providers:
 *
 * @see \ILIAS\Services\WOPI\Embed\EmbeddedApplicationGSProvider, \ILIAS\WOPI\Embed\EmbeddedApplicationGSProvider
 * @see \ILIAS\Container\Screen\MemberViewLayoutProvider
 * @see \ILIAS\LTI\Screen\LtiViewLayoutProvider
 * @see \ilLSViewLayoutProvider
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverImpersonationPageModificationProvider extends AbstractModificationPluginProvider
{
    use ilUserTakeOverImpersonationTarget;

    protected ilUserTakeOverImpersonationHandler $impersonation_handler;
    protected ilUserTakeOverAccessHandler $access_handler;
    protected ILIAS\Data\Factory $data_factory;
    protected ILIAS\UI\Factory $ui_factory;
    protected MetaContent $meta_content;
    protected ITranslator $translator;
    /** @var ModificationProvider[] */
    protected array $conflicting_core_providers = [];
    protected bool $is_initiaised = false;
    protected $if;

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->internal()->external();
    }

    public function getPageBuilderDecorator(CalledContexts $screen_context_stack): ?PageBuilderModification
    {
        $this->init();

        if (!$this->impersonation_handler->isImpersonationActive()) {
            return null;
        }

        // abort if any of the conflicted providers returns a modification.
        foreach ($this->conflicting_core_providers as $conflicting_provider) {
            if ($conflicting_provider->isInterestedInContexts()->hasMatch($screen_context_stack) &&
                null !== $conflicting_provider->getPageBuilderDecorator($screen_context_stack)
            ) {
                return null;
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factory->page()->withModification(
            fn(PagePartProvider $provider): Page => $this->decorateStandardPage($this->buildStandardPage($provider)),
        )->withHighPriority();
    }

    /**
     * Mostly copied from @see StandardPageBuilder::build()
     */
    protected function buildStandardPage(PagePartProvider $provider): Page
    {
        $this->init();

        $meta_bar = $provider->getMetaBar();
        $main_bar = $provider->getMainBar();
        $bread_crumbs = $provider->getBreadCrumbs();
        $header_image = $provider->getLogo();
        $responsive_header_image = $provider->getResponsiveLogo();
        $favicon_path = $provider->getFaviconPath();
        $footer = $provider->getFooter();
        $title = $provider->getTitle();
        $short_title = $provider->getShortTitle();
        $view_title = $provider->getViewTitle();
        $toast_container = $provider->getToastContainer();

        $page = $this->ui_factory->layout()->page()->standard(
            [$provider->getContent()],
            $meta_bar,
            $main_bar,
            $bread_crumbs,
            $header_image,
            $responsive_header_image,
            $favicon_path,
            $toast_container,
            $footer,
            $title,
            $short_title,
            $view_title,
        );

        foreach ($this->meta_content->getMetaData() as $meta_datum) {
            $page = $page->withAdditionalMetaDatum($meta_datum);
        }
        if (null !== ($og_meta_data = $this->meta_content->getOpenGraphMetaData())) {
            $page = $page->withAdditionalMetaDatum($og_meta_data);
        }

        $page = $page->withSystemInfos($provider->getSystemInfos());
        $page = $page->withTextDirection($this->meta_content->getTextDirection() ?? Standard::LTR);

        return $page;
    }

    /**
     * This needs to be called because we cannot override the constructor.
     * This is idempotent, can be called multiple times without re-initialising.
     */
    protected function init(): void
    {
        if ($this->is_initiaised) {
            return;
        }

        /** @var $plugin ilUserTakeOverPlugin */
        $plugin = $this->plugin;
        $this->translator = $plugin;

        $this->conflicting_core_providers = [
            new \ILIAS\Container\Screen\MemberViewLayoutProvider($this->dic),
            new \ILIAS\LTI\Screen\LtiViewLayoutProvider($this->dic),
            new ilLSViewLayoutProvider($this->dic),
        ];

        // for ILIAS<=v9.14:
        if (class_exists('\ILIAS\Services\WOPI\Embed\EmbeddedApplicationGSProvider')) {
            $this->conflicting_core_providers[] = new \ILIAS\Services\WOPI\Embed\EmbeddedApplicationGSProvider($this->dic);
        }
        // for ILIAS>=v9.15:
        if (class_exists('\ILIAS\WOPI\Embed\EmbeddedApplicationGSProvider')) {
            $this->conflicting_core_providers[] = new \ILIAS\WOPI\Embed\EmbeddedApplicationGSProvider($this->dic);
        }

        $this->access_handler = new \ilUserTakeOverAccessHandler(
            new \ilUserTakeOverGroupRepository($this->dic->database()),
            (new \ilUserTakeOverSettingsRepository($this->dic->database()))->get(),
            $this->dic->http()->wrapper()->query(),
            $this->dic->refinery(),
            $this->dic->user(),
            $this->dic->rbac()->review(),
            $this->dic->rbac()->system()
        );
        $this->impersonation_handler = new \ilUserTakeOverImpersonationHandler(
            new \ilUserTakeOverGeneralRepository($this->dic->rbac()->review()),
            $this->translator,
            $this->access_handler,
            new \ilUserTakeOverSessionWrapper(),
            $this->dic->refinery(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ctrl(),
            $this->dic->user()
        );
        $this->meta_content = $this->dic->globalScreen()->layout()->meta();
        $this->ui_factory = $this->dic->ui()->factory();
        $this->data_factory = new ILIAS\Data\Factory();
        $this->is_initiaised = true;
    }

    protected function decorateStandardPage(Page $page): Page
    {
        $this->init();

        $target = $this->getImpersonateTarget($this->impersonation_handler->getOriginalUser());
        $target = $this->data_factory->uri(ILIAS_HTTP_PATH . "/$target");

        $page = $page->withModeInfo(
            $this->ui_factory->mainControls()->modeInfo($this->translator->txt(ITranslator::TOOL_TITLE_LEAVE), $target)
        );

        return $page;
    }
}
