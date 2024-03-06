<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form;

use srag\Plugins\UserTakeOver\IRequestParameters;
use Closure;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait TagInputAutoCompleteBinder
{
    /**
     * Extends a tag-input to load tags or options from the given ajax source.
     * To use this, withAdditionalOnLoadCode() must be used with the returned
     * closure.
     *
     * The ajax source must return array[], whereas the sub-arrays have the keys
     * 'value', 'display', and 'searchBy'.
     */
    protected function getTagInputAutoCompleteBinder(string $ajax_action): Closure
    {
        return static function ($id) use ($ajax_action): string {
            return "
                var {$id}_requests = [];
                let searchCategories = async function (event) {
                    let tag = il.UI.Input.tagInput.getTagifyInstance('$id')
                    let value = event.detail.value;

                    // abort if value has not at least two characters.
                    // if (2 < value.length) { return; }

                    // show the loading animation and hide the suggestions.
                    tag.loading(true);
                    tag.dropdown.hide();

                    // kill the last request before starting a new one.
                    if (0 < {$id}_requests.length) {
                        for (let i = 0; i < {$id}_requests.length; i++) {
                            {$id}_requests[i].abort();
                        }
                    }

                    // fetch suggestions asynchronously and store the
                    // current request in the array.
                    {$id}_requests.push($.ajax({
                        type: 'GET',
                        url: encodeURI('$ajax_action' + '& " . IRequestParameters::SEARCH_TERM . "=' + value),
                        success: response => {
                            // update whitelist, hide loading animation and
                            // show the suggestions.
                            tag.settings.whitelist = response;
                            tag.loading(false);
                            tag.dropdown.show();
                        },
                    }));
                }

                $(document).ready(function () {
                    let tag = il.UI.Input.tagInput.getTagifyInstance('$id');

                    // enforceWhitelist will make the whitelist persistent,
                    // previously found objects will therefore stay in it. 
                    tag.on('input', searchCategories);
                });
            ";
        };
    }
}
