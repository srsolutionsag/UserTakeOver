<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\UI\Form\TagInputAutoCompleteBinder;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as Components;
use ILIAS\UI\Renderer;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverMetaBarProvider extends AbstractStaticMetaBarPluginProvider
{
    use ilUserTakeOverImpersonationTarget;
    use ilUserTakeOverPluginInstance;
    use ilUserTakeOverDisplayName;
    use TagInputAutoCompleteBinder;

    protected const ICON_DIRECTORY = 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/images/';

    protected ilUserTakeOverImpersonationHandler $impersonation_handler;
    protected ilUserTakeOverAccessHandler $access_handler;
    protected ilUserTakeOverSessionWrapper $session;
    protected ilCtrlInterface $ctrl;
    protected ilObjUser $current_user;
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

        $this->access_handler = new ilUserTakeOverAccessHandler(
            new ilUserTakeOverGroupRepository($this->dic->database()),
            (new ilUserTakeOverSettingsRepository($this->dic->database()))->get(),
            $this->dic->http()->wrapper()->query(),
            $this->dic->refinery(),
            $this->dic->user(),
            $this->dic->rbac()->review(),
            $this->dic->rbac()->system()
        );

        $this->impersonation_handler = new ilUserTakeOverImpersonationHandler(
            new ilUserTakeOverGeneralRepository($this->dic->rbac()->review()),
            $plugin,
            $this->access_handler,
            new ilUserTakeOverSessionWrapper(),
            $this->dic->refinery(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ctrl(),
            $this->dic->user()
        );

        $this->general_repository = new ilUserTakeOverGeneralRepository($this->dic->rbac()->review());
        $this->group_repository = new ilUserTakeOverGroupRepository($this->dic->database());
        $this->session = new ilUserTakeOverSessionWrapper();
        $this->factory = $this->dic->ui()->factory();
        $this->renderer = $this->dic->ui()->renderer();
        $this->refinery = $this->dic->refinery();
        $this->current_user = $this->dic->user();
        $this->ctrl = $this->dic->ctrl();
        $this->translator = $plugin;

        $this->is_initialized = true;
    }

    /**
     * @inheritDoc
     */
    public function getMetaBarItems(): array
    {
        $this->init();

        if ($this->impersonation_handler->isImpersonationActive()) {
            return [
                $this->meta_bar
                    ->linkItem($this->getItemId('leave'))
                    ->withTitle($this->translator->txt(ITranslator::TOOL_TITLE_LEAVE))
                    ->withSymbol($this->getIcon('leave.svg'))
                    ->withAction($this->getImpersonateTarget($this->impersonation_handler->getOriginalUser()))
            ];
        }

        $items[] = $this->meta_bar
            ->topLegacyItem($this->getItemId('search'))
            ->withTitle($this->translator->txt(ITranslator::TOOL_TITLE_SEARCH))
            ->withSymbol($this->getIcon('search.svg'))
            ->withLegacyContent(
                $this->factory->legacy($this->renderer->render($this->getUserSearchItem()))
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
                    fn () => !empty($items) &&
                        $this->plugin->isActive() &&
                        (
                            $this->access_handler->canCurrentUserUsePlugin() ||
                            $this->impersonation_handler->isImpersonationActive()
                        )
                ),
        ];
    }

    protected function getUserSearchItem(): Component
    {
        return $this->factory->input()->container()->form()->standard('#', [
            $this->factory->input()->field()->tag(
                $this->translator->txt(ITranslator::TOOL_TITLE_SEARCH),
                []
            )->withMaxTags(1)->withAdditionalOnLoadCode(
                $this->getTagInputAutoCompleteBinder(
                    $this->ctrl->getLinkTargetByClass(
                        [ilObjPluginDispatchGUI::class, ilUserTakeOverGroupGUI::class],
                        ilUserTakeOverGroupGUI::CMD_FIND_TARGETS,
                        null,
                        true
                    )
                )
            )->withAdditionalOnLoadCode(fn ($id) => "
                // this snippet was sponsored by ChatGPT :):
                function findFirstNumber(arr) {
                  for (let i = 0; i < arr.length; i++) {
                    const parsedValue = parseInt(arr[i]);
                    if (!isNaN(parsedValue)) {
                      return parsedValue;
                    }
                  }
                  return null;
                }

                const input = document.getElementById('$id');
                input?.closest('form')?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const value = input.value.split(',');
                    let userId = findFirstNumber(value);
                    if (null !== userId) {
                        window.location = '" . ILIAS_HTTP_PATH . "/" . $this->getImpersonateTarget(null) . "' + userId;
                    }
                });
            ")
        ])->withSubmitCaption($this->translator->txt(ITranslator::GENERAL_ACTION_IMPERSONATE));
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
                fn () => !empty($member_items) && !$this->impersonation_handler->isImpersonationActive()
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
