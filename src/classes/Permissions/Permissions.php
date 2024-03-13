<?php

declare(strict_types = 1);

namespace Permissions;

class Permissions
{
    public function __construct() {
        if (!file_exists(EXTR_ROOT_DIR . '/config/user/permissions.php')) {
            throw new \Exception('Missing user configuration, please install the system first!');
        }

        require_once EXTR_ROOT_DIR . '/config/user/permissions.php'; // TODO: permissions file, which also defines if this system should check for permissions internally or use something external.
    }


}
