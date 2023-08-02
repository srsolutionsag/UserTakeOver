<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form;

use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ilObject2;
use Closure;
use srag\Plugins\UserTakeOver\IRequestParameters;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormBuilder implements IFormBuilder
{
    use TagInputAutoCompleteBinder;

    protected ITranslator $translator;
    protected FormFactory $forms;
    protected FieldFactory $fields;
    protected Refinery $refinery;
    protected string $form_action;

    public function __construct(
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        string $form_action
    ) {
        $this->translator = $translator;
        $this->forms = $forms;
        $this->fields = $fields;
        $this->refinery = $refinery;
        $this->form_action = $form_action;
    }

    /**
     * Validates submitted numeric values against an allowed range between
     * $minimum and $maximum.
     */
    protected function getIntegerRangeValidationConstraint(int $minimum, int $maximum): Constraint
    {
        return $this->refinery->custom()->constraint(
            static function ($number) use ($minimum, $maximum): bool {
                if (!is_numeric($number)) {
                    return false;
                }

                return ($minimum <= $number && $number >= $maximum);
            },
            sprintf(
                $this->translator->txt(ITranslator::MSG_NUMBER_OUT_OF_RANGE),
                $minimum,
                $maximum
            )
        );
    }

    /**
     * Validates submitted numeric values against an allowed range between
     * $minimum and $maximum.
     */
    protected function getTextLengthValidationConstraint(int $minimum, int $maximum): Constraint
    {
        return $this->refinery->custom()->constraint(
            static function ($text) use ($minimum, $maximum): bool {
                $length = strlen((string) $text);
                return ($minimum <= $length && $length <= $maximum);
            },
            sprintf(
                $this->translator->txt(ITranslator::MSG_TEXT_OUT_OF_RANGE),
                $minimum,
                $maximum
            )
        );
    }

    /**
     * Validates submitted numeric inputs, if the value is not an object ref-id
     * an according message is displayed in the form.
     */
    protected function getRefIdValidationConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (int $ref_id): bool {
                return (ilObject2::_exists($ref_id, true));
            },
            $this->translator->txt(ITranslator::MSG_INVALID_REF_ID)
        );
    }

    /**
     * Behaves similar to @see AbstractFormBuilder::getRefIdValidationConstraint(),
     * but accepts an array of ref-ids that are validated.
     */
    protected function getRefIdArrayValidationConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (array $ref_ids): bool {
                foreach ($ref_ids as $ref_id) {
                    if (!ilObject2::_exists((int) $ref_id, true)) {
                        return false;
                    }
                }

                return true;
            },
            $this->translator->txt(ITranslator::MSG_INVALID_REF_IDS)
        );
    }

    /**
     * Returns a validation constraint for text-inputs that can be used to check
     * if a valid email-address has been submitted.
     */
    protected function getEmailValidationConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            static function (string $email): bool {
                if (!empty($email)) {
                    return is_string(filter_var($email, FILTER_VALIDATE_EMAIL));
                }

                // the constraint should pass if there was no submitted email.
                return true;
            },
            $this->translator->txt(ITranslator::MSG_INVALID_EMAIL)
        );
    }
}
