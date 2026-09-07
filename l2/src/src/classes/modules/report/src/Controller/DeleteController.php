<?php

namespace idoit\Module\Report\Controller;

use idoit\Module\Report\Event\DeleteReport;
use idoit\Module\Report\Event\NotifyReportDeletion;
use isys_application;
use isys_auth_report;
use isys_component_template_language_manager;
use isys_format_json;
use isys_module_report;
use isys_report_dao;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * i-doit
 *
 * Delete controller.
 *
 * @package     i-doit
 * @subpackage  Document
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-10975
 */
class DeleteController
{
    private isys_auth_report $auth;
    private isys_component_template_language_manager $language;
    private EventDispatcher $dispatcher;
    private int $currentUserId;

    public function __construct()
    {
        $this->auth = isys_module_report::getAuth();
        $this->language = isys_application::instance()->container->get('language');
        $this->dispatcher = isys_application::instance()->container->get('event_dispatcher');
        $this->currentUserId = (int)isys_application::instance()->container->get('session')->get_user_id();
    }

    /**
     * @param Request $request
     * @return array<int>
     * @throws \idoit\Exception\JsonException
     */
    private function getReportIds(Request $request): array
    {
        if (!isys_format_json::is_json_array($request->get('ids'))) {
            return [];
        }

        $reportIds = array_map('intval', isys_format_json::decode($request->get('ids')));

        return array_unique(array_filter($reportIds, fn ($id) => $id > 0));
    }

    public function checkDeletion(Request $request): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => null,
            'message' => ''
        ];

        try {
            $dao = isys_report_dao::instance();
            $reportIds = $this->getReportIds($request);

            foreach ($reportIds as $id) {
                if ($dao->isSelfCreated((int)$id)) {
                    $this->auth->check($this->auth::DELETE, 'SELF_CREATED_REPORTS');
                } else {
                    $this->auth->check($this->auth::DELETE, 'CUSTOM_REPORT/' . $id);
                }
            }

            $response['data'] = $this->language->get('LC__REPORT__PURGE_CONFIRM');

            try {
                $event = new NotifyReportDeletion($reportIds);
                $this->dispatcher->dispatch($event, $event::NAME);

                if (count($event->getMessages())) {
                    $response['data'] .= PHP_EOL . PHP_EOL . implode(PHP_EOL, $event->getMessages());
                }
            } catch (Throwable $e) {
                // Do nothing, it is possible that some 'third-party' code is throwing exceptions.
            }
        } catch (Throwable $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    public function deleteReport(Request $request): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => null,
            'message' => ''
        ];

        try {
            $dao = isys_report_dao::instance();

            foreach ($this->getReportIds($request) as $id) {
                if ($dao->isSelfCreated($id)) {
                    $this->auth->check($this->auth::DELETE, 'SELF_CREATED_REPORTS');
                } else {
                    $this->auth->check($this->auth::DELETE, 'CUSTOM_REPORT/' . $id);
                }

                try {
                    $this->dispatcher->dispatch(new DeleteReport($id), DeleteReport::NAME);
                } catch (Throwable $e) {
                    // Do nothing, it is possible that some 'third-party' code is throwing exceptions.
                }

                $dao->deleteReport($id);
            }
        } catch (Throwable $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }
}
