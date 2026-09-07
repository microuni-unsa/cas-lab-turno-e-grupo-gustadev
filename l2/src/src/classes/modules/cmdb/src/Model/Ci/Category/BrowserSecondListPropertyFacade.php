<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

use idoit\Module\Cmdb\Model\Ci\Category\G\Application\AssignedDatabases;
use idoit\Module\Cmdb\Model\Ci\Category\G\AssignedSImCards\AssignedSimCards;
use idoit\Module\Cmdb\Model\Ci\Category\G\Ip\DnsServer;
use idoit\Module\Cmdb\Model\Ci\Category\G\LdevClient\AssignedLdevServer;
use idoit\Module\Cmdb\Model\Ci\Category\G\LdevServer\AssignedLdevClient;
use idoit\Module\Cmdb\Model\Ci\Category\S\Net\DnsServer as NetDnsServer;

class BrowserSecondListPropertyFacade
{
    /**
     * @return BrowserSecondListPropertyProvider
     */
    public static function getService(): BrowserSecondListPropertyProvider
    {
        return new BrowserSecondListPropertyProvider([
            new AssignedDatabases(),
            new AssignedSimCards(),
            new DnsServer(),
            new AssignedLdevClient(),
            new AssignedLdevServer(),
            new NetDnsServer()
        ]);
    }
}
