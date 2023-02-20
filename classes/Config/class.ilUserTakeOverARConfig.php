<?php

/**
 * ilUserTakeOverARConfig stores all plugin configurations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class is used to store any sort of value for a specific CONFIGURATION_IDENTIFIER.
 * Since any type of value is accepted by the setValue(), the data will be encoded to
 * JSON and stored as TEXT in the database. Therefore values have to be type-casted in
 * most cases before used.
 *
 * setValue() and getValue() although distinguish arrays from other values to save
 * developers the trouble of exploding strings. Therefore getValue() will return both,
 * strings and arrays.
 *
 * - general usage:
 *
 *      - load configuration:
 *
 *          $config = ilUserTakeOverARConfig::get();
 *          $option = $config[ilUserTakeOverARConfig::<<CONFIGURATION_IDENTIFIER>>]->getValue();
 *
 *          or
 *
 *          $config = ilUserTakeOverARConfig::find(ilUserTakeOverARConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $option = $config->getValue();
 *
 *      - update configuration:
 *
 *          $config = ilUserTakeOverARConfig::find(ilUserTakeOverARConfig::<<CONFIGURATION_IDENTIFIER>>);
 *          $config
 *              ->setValue(mixed $value)
 *              ->store();
 */
final class ilUserTakeOverARConfig extends ActiveRecord
{
    /**
     * @var string primary key regex pattern
     */
    private const IDENTIFIER_REGEX = '/^[A-Za-z0-9_-]*$/';

    /**
     * @var string active record table name
     */
    public const TABLE_NAME = ilUserTakeOverPlugin::PLUGIN_ID . '_config';

    /**
     * @var string identifier name
     */
    public const IDENTIFIER = 'identifier';

    /**
     * configuration identifiers
     */
    public const CNF_ID_GLOBAL_ROLES = 'cnf_global_role_ids';
    public const CNF_ALLOW_IMPERSONATE_ADMINS = 'cnf_allow_impersonate_admins';

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_unique   true
     * @con_is_primary  true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      250
     */
    protected $identifier;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_is_notnull  true
     * @con_fieldtype   text
     * @con_length      4000
     */
    protected $value;

    /**
     * checks primary key value for prohibited characters.
     *
     * @param string $identifier
     * @throws arException
     */
    private function validateIdentifier(string $identifier) : void
    {
        if (!preg_match(self::IDENTIFIER_REGEX, $identifier)) {
            throw new arException(
                arException::UNKNONWN_EXCEPTION,
                'Prohibited characters in primary key value $identifier: ' . $identifier
            );
        }
    }

    /**
     * @return string
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return ilUserTakeOverARConfig
     * @throws arException
     */
    public function setIdentifier(string $identifier) : ilUserTakeOverARConfig
    {
        $this->validateIdentifier($identifier);
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getValue()
    {
        $value = json_decode($this->value, true);
        if (empty($value)) return null;
        if (is_array($value) && !empty((array) $value)) {
            return (array) $value;
        }

        // remove quotes which come from json_decode() in strings
        return trim($value, '"');
    }

    /**
     * @param mixed $value
     * @return ilUserTakeOverARConfig
     */
    public function setValue($value) : ilUserTakeOverARConfig
    {
        if (!is_array((array) $value)) {
            // lowercase string values for easier comparisons
            $value = strtolower((string) $value);
        }

        $this->value = json_encode($value);
        return $this;
    }
}
