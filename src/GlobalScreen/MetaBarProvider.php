<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use srag\Plugins\UserTakeOver\UI\Form\TagInputAutoCompleteBinder;
use ILIAS\UI\Factory as Components;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as Refinery;
use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Symbol\Symbol;
use srag\Plugins\UserTakeOver\Group\Group;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;

class MetaBarProvider extends AbstractStaticMetaBarPluginProvider
{
    use \ilUserTakeOverImpersonationTarget;
    use \ilUserTakeOverPluginInstance;
    use \ilUserTakeOverDisplayName;
    use TagInputAutoCompleteBinder;

    protected const ICON_DIRECTORY = 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/images/';

    protected \ilUserTakeOverImpersonationHandler $impersonation_handler;
    protected \ilUserTakeOverAccessHandler $access_handler;
    protected \ilUserTakeOverSessionWrapper $session;
    protected \ilCtrlInterface $ctrl;
    protected \ilObjUser $current_user;
    protected Components $factory;
    protected Renderer $renderer;
    protected Refinery $refinery;
    protected IGeneralRepository $general_repository;
    protected IGroupRepository $group_repository;
    protected ITranslator $translator;

    protected bool $is_initialized = false;

    /**
     * This method initializes this provider. This method is required due to the
     * final constructor of the parent class and for ease of use for our own
     * dependencies.
     */
    public function init(): void
    {
        if ($this->is_initialized) {
            return;
        }

        $plugin = $this->getPlugin($this->dic);

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
            $plugin,
            $this->access_handler,
            new \ilUserTakeOverSessionWrapper(),
            $this->dic->refinery(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ctrl(),
            $this->dic->user()
        );

        $this->general_repository = new \ilUserTakeOverGeneralRepository($this->dic->rbac()->review());
        $this->group_repository = new \ilUserTakeOverGroupRepository($this->dic->database());
        $this->session = new \ilUserTakeOverSessionWrapper();
        $this->factory = $this->dic->ui()->factory();
        $this->renderer = $this->dic->ui()->renderer();
        $this->refinery = $this->dic->refinery();
        $this->current_user = $this->dic->user();
        $this->ctrl = $this->dic->ctrl();
        $this->translator = $plugin;

        $this->is_initialized = true;
    }

    public function getMetaBarItems(): array
    {
        $items = [];
        $this->init();

        // Status & Leave
        if ($this->impersonation_handler->isImpersonationActive()) {
            $items[] = (new StatusItem(
                $this->getItemId('uto_status'),
                $this->dic->ui()->factory(),
                $this->impersonation_handler->getOriginalUser()->getId(),
                $this->translator
            ))->withTitle($this->translator->txt('status'))
              ->withSymbol($this->getIcon('info.svg'));

            $items[] = $this->meta_bar->linkItem($this->getItemId('uto_leave'))
                                      ->withTitle($this->translator->txt(ITranslator::TOOL_TITLE_LEAVE))
                                      ->withAction(
                                          $this->getImpersonateTarget($this->impersonation_handler->getOriginalUser())
                                      )
                                      ->withSymbol($this->getIcon('leave.svg'));
        }

        $items[] = (new SearchItem($this->getItemId('search'), $this->dic->ui()->factory()))
            ->withVisibilityCallable(
                fn (): bool => $this->access_handler->canCurrentUserUsePlugin()
            )->withSymbol($this->getIcon('search.svg'))
            ->withUrl(
                $this->ctrl->getLinkTargetByClass(
                    [\ilObjPluginDispatchGUI::class, \ilUserTakeOverGroupGUI::class],
                    \ilUserTakeOverGroupGUI::CMD_FIND_TARGETS,
                    null,
                    true
                )
            )
            ->withTitle(
                $this->translator->txt(
                    ITranslator::TOOL_TITLE_SEARCH
                )
            );

        $groups = $this->group_repository->getGroupsOfUser($this->dic->user()->getId());
        foreach ($groups as $index => $group) {
            $items[] = $this->getGroupItem($group, $index);
        }

        return [
            $this->meta_bar
                ->topParentItem($this->getItemId(''))
                ->withChildren($items)
                ->withTitle($this->translator->txt(ITranslator::TOOL_TITLE))
                ->withSymbol($this->getIcon('index.svg'))
                ->withVisibilityCallable(
                    fn (): bool => !empty($items) &&
                        $this->plugin->isActive() &&
                        (
                            $this->access_handler->canCurrentUserUsePlugin() ||
                            $this->impersonation_handler->isImpersonationActive()
                        )
                ),
        ];
    }

    protected function getGroupItem(Group $group, int $count): isItem
    {
        $group_id = "group_$count";

        $member_items = [];
        foreach ($group->getGroupMembers() as $index => $user_id) {
            if ($this->current_user->getId() === $user_id) {
                continue;
            }

            $user = $this->general_repository->getUser($user_id);
            if (!$this->access_handler->canCurrentUserImpersonate($user)) {
                continue;
            }

            $member_items[] = $this->meta_bar
                ->linkItem($this->getItemId("{$group_id}_$index"))
                ->withSymbol($this->getIcon('user.svg', true))
                ->withAction($this->getImpersonateTarget($user))
                ->withTitle($this->getDisplayName($user));
        }

        return $this->meta_bar
            ->topParentItem($this->getItemId($group_id))
            ->withSymbol($this->getIcon('group.svg'))
            ->withTitle($group->getTitle())
            ->withChildren($member_items)
            ->withVisibilityCallable(
                // this is most likely an ILIAS bug, because this callable will not be used
                // and the item will be shown without any items anyways.
                fn (): bool => $member_items !== [] && !$this->impersonation_handler->isImpersonationActive()
            );
    }

    protected function getIcon(string $icon, bool $is_small = false): Symbol
    {
        return $this->factory->symbol()->icon()->custom(
            self::ICON_DIRECTORY . $icon,
            $this->translator->txt(ITranslator::TOOL_TITLE),
            ($is_small) ? 'small' : 'medium'
        );
    }

    protected function getItemId(string $item_name): IdentificationInterface
    {
        return $this->if->identifier("{$this->plugin->getId()}_$item_name");
    }

}
