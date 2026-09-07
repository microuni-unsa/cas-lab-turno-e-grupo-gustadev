<?php

namespace idoit\Component\Notify\Handler;

use idoit\Component\Notify\Interfaces\HandlerInterface;
use idoit\Component\Notify\Interfaces\NotificationInterface;
use Smarty\Smarty;

/**
 * Class SmartyHandler
 *
 * Sends notifications directly to the template system.
 */
class SmartyHandler implements HandlerInterface
{
    /**
     * @var Smarty
     */
    private Smarty $smarty;

    /**
     * Handle a notification.
     *
     * @param NotificationInterface $notification
     * @param int                   $level
     *
     * @return void
     */
    public function handle(NotificationInterface $notification, int $level): void
    {
        $options = $notification->attributes();
        $options['header'] = $notification->title();

        $variable = [
            'message' => $notification->message(),
            'type'    => $level,
            'options' => $options
        ];

        $current = $this->smarty->getTemplateVars('notification');

        if (!$current) {
            $this->smarty->assign('notification', [$variable]);
        } else {
            $this->smarty->append('notification', $variable);
        }
    }

    /**
     * SmartyHandler constructor.
     *
     * @param Smarty $template
     */
    public function __construct(Smarty $template)
    {
        $this->smarty = $template;
    }
}
