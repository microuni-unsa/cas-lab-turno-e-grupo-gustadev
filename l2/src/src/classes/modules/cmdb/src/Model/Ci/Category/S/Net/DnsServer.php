<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\S\Net;

use idoit\Module\Cmdb\Model\Ci\Category\G\Ip\DnsServer as IpDnsServer;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;

class DnsServer extends IpDnsServer implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_s_net';

    /**
     * @var string
     */
    protected static string $method = 'object_browser2';
}
