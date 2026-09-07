<?php

/**
 *
 */
class isys_ajax_handler_dashboard_widgets_loggedinusers extends isys_ajax_handler_dashboard
{
    /**
     * @return void
     * @throws isys_exception_database
     */
    public function init()
    {
        $l_locales = isys_application::instance()->container->get('locales');

        $l_quicky = new isys_ajax_handler_quick_info();

        $l_dao = isys_application::instance()->container->get('cmdb_dao');

        $l_time = time() - 300; // 5 minutes
        $l_datetime = date('Y-m-d H:i:s', $l_time);

        $l_sql = 'SELECT isys_cats_person_list__isys_obj__id AS id, isys_cats_person_list__title AS username,
			isys_cats_person_list__first_name AS first_name, isys_cats_person_list__last_name AS last_name,
			MAX(isys_user_session__time_last_action) AS last_action FROM isys_cats_person_list
			INNER JOIN isys_user_session ON isys_user_session__isys_obj__id = isys_cats_person_list__isys_obj__id
			GROUP BY isys_user_session__isys_obj__id
			HAVING last_action > ' . $l_dao->convert_sql_text($l_datetime);

        $l_res = $l_dao->retrieve($l_sql);
        $l_options = '';

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                [$l_last_action_date, $l_last_action_time] = explode(' ', $l_row['last_action']);

                $l_options .= '<tr>' .
                    '<td>' . $l_quicky->get_quick_info($l_row['id'], $l_row['username'], C__LINK__OBJECT) . '</td>' .
                    '<td>' . $l_locales->fmt_date(strtotime($l_last_action_date)) . ' ' . $l_locales->fmt_time($l_last_action_time, false) . '</td>' .
                    '</tr>';
            }
        }

        echo $l_options;
        $this->_die();
    }

    /**
     * @return bool
     */
    public static function needs_hypergate()
    {
        return true;
    }
}
