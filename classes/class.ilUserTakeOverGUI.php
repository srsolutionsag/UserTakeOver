<?php

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class ilUserTakeOverGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilUserTakeOverGUI: ilUIPluginRouterGUI
 */
class ilUserTakeOverGUI
{
    use DICTrait;

    const CMD_SEARCH = 'search';
    const CMD_INDEX = 'index';

    public function executeCommand()
    {
        switch (self::dic()->ctrl()->getCmd(self::CMD_INDEX)) {
            case self::CMD_SEARCH:
                $this->search();
                break;
            default:
                break;
        }
    }

    private function search()
    {
        $term = "%" . self::dic()->http()->request()->getQueryParams()['q'] . "%";

        $q = "SELECT usr_id, firstname, lastname, login FROM usr_data 
                WHERE 
                firstname LIKE %s OR 
                lastname LIKE %s OR 
                email LIKE %s OR 
                login LIKE %s";

        $r    = self::dic()->database()->queryF($q, ['text', 'text', 'text', 'text'], [$term, $term, $term, $term]);
        $json = [];
        while ($d = self::dic()->database()->fetchObject($r)) {
//            $json[$d->usr_id] = "{$d->firstname} {$d->lastname}";
            $json[] = $d;
        }
        $result = [
            'title' => 'Results',
            'data'  => $json
        ];
        echo json_encode($json);
        exit;
    }
}
