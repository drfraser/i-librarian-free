<?php

namespace LibrarianApp;

use Exception;
use Librarian\Html\Bootstrap;
use Librarian\Media\Temporal;
use Librarian\Mvc\TextView;

class DashboardView extends TextView {

    use SharedHtmlView;

    /**
     * @var Temporal
     */
    private $temporal;

    /**
     * @param $data
     * @return string
     * @throws Exception
     */
    public function main($data) {

        $this->title('Dashboard');

        $this->head();

        /** @var Bootstrap\Breadcrumb $el */
        $el = $this->di->get('Breadcrumb');

        $el->style('margin: 0 -15px');
        $el->addClass('bg-transparent');
        $el->item('IL', '#dashboard');
        $el->item("Dashboard");
        $bc = $el->render();

        $el = null;

        // Cards array.
        $cards = [];

        /*
         * Quick search.
         */

        if ($this->app_settings->getUser('dashboard_remove_search') === '0') {

            /** @var Bootstrap\Button $el */
            $el = $this->di->get('Button');

            $el->context('link');
            $el->addClass('px-1');
            $el->attr('data-dismiss', 'modal');
            $el->attr('data-toggle', 'modal');
            $el->dataTarget('#modal-advanced-search');
            $el->html('Advanced search');
            $advanced_button = $el->render();

            $el = null;

            /** @var Bootstrap\Button $el */
            $el = $this->di->get('Button');

            $el->context('link');
            $el->addClass('px-1 ml-2');
            $el->attr('data-dismiss', 'modal');
            $el->attr('data-toggle', 'modal');
            $el->dataTarget('#modal-searches');
            $el->html('Previous searches');
            $searches_button = $el->render();

            $el = null;

            $search_html = <<<EOT
                {$this->sharedQuickSearch()}
                $advanced_button
                $searches_button
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<b>SEARCH</b>', 'px-4 pt-3');
            $el->body($search_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Activity.
         */

        if ($this->app_settings->getUser('dashboard_remove_stats') === '0') {

            $count = $this->scalar_utils->formatNumber($data['count']);

            $activ_html = <<<EOT
                <div style="position:relative;width:100%;height:200px"><canvas id="myChart"></canvas></div>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header("<b>$count ITEMS</b>", 'px-4 pt-3');
            $el->body($activ_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Last items.
         */

        $id_list = [];

        if ($this->app_settings->getUser('dashboard_remove_items') === '0') {

            $last5_html = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_items']) === 0) {

                $last5_html .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="text-center text-secondary pb-4 align-middle">
                            NO ITEMS
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_items'] as $item) {

                $id_list[] = $item['id'];

                $item_url = IL_BASE_URL . 'index.php/item#summary?id=' . $item['id'];
                $pdf_button = '<span class="badge border border-secondary text-secondary rounded-0 mr-2">PDF</span>';

                if ($item['has_pdf'] === '1') {

                    $pdf_url = IL_BASE_URL . 'index.php/pdf?id=' . $item['id'];
                    $pdf_button = <<<PDF
                        <a href="{$pdf_url}"><span class="badge badge-warning rounded-0 mr-2">PDF</span></a>
PDF;
                }

                $last5_html .= <<<EOT
                    <tr>
                        <td class="text-truncate">
                            $pdf_button
                            <a href="{$item_url}">{$item['title']}</a>
                        </td>
                    </tr>
EOT;
            }

            $last5_html .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<span><b>ITEMS</b> <a class="mx-3" href="#items/main">List</a> <a href="#items/filter">Filter</a></span>', 'px-4 pt-3');
            $el->body($last5_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Last projects.
         */

        if ($this->app_settings->getUser('dashboard_remove_projects') === '0') {

            $project_html = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_projects']) === 0) {

                $project_html .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="align-middle text-center text-secondary pb-4">
                            NO PROJECTS
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_projects'] as $project) {

                $item_url = IL_BASE_URL . 'index.php/project#project/browse?id=' . $project['id'];

                $project_html .= <<<EOT
                    <tr>
                        <td class="text-truncate">
                            <a href="{$item_url}">{$project['project']}</a>
                        </td>
                    </tr>
EOT;
            }

            $project_html .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<span><b>PROJECTS</b> <a class="ml-3" href="#projects/main">List</a></span>', 'px-4 pt-3');
            $el->body($project_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Last notes.
         */

        if ($this->app_settings->getUser('dashboard_remove_item_notes') === '0') {

            $notes_html = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_notes']) === 0) {

                $notes_html .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="text-center text-secondary align-middle pb-4">
                            NO NOTES
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_notes'] as $item) {

                $item_url = IL_BASE_URL . 'index.php/item#notes?id=' . $item['id'];

                $note = $this->sanitation->lmth($item['note']);

                $notes_html .= <<<EOT
                    <tr>
                        <td class="text-truncate" style="direction: rtl">
                            <a href="{$item_url}">{$note}&lrm;</a>
                        </td>
                    </tr>
EOT;
            }

            $notes_html .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<b>ITEM NOTES</b>', 'px-4 pt-3');
            $el->body($notes_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Project notes.
         */

        if ($this->app_settings->getUser('dashboard_remove_project_notes') === '0') {

            $project_notes = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_project_notes']) === 0) {

                $project_notes .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="text-center text-secondary align-middle pb-4">
                            NO NOTES
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_project_notes'] as $project) {

                $item_url = IL_BASE_URL . 'index.php/project#project/notes?id=' . $project['id'];

                $project_notes .= <<<EOT
                    <tr>
                        <td class="text-truncate">
                            <a href="{$item_url}">{$project['note']}</a>
                        </td>
                    </tr>
EOT;
            }

            $project_notes .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<b>PROJECT NOTES</b>', 'px-4 pt-3');
            $el->body($project_notes, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Last items discussed.
         */

        if ($this->app_settings->getUser('dashboard_remove_item_discussions') === '0') {

            $discussed_html = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_discussed']) === 0) {

                $discussed_html .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="text-center text-secondary align-middle pb-4">
                            NO POSTS
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_discussed'] as $item) {

                $item_url = IL_BASE_URL . 'index.php/item#itemdiscussion?id=' . $item['id'];

                $discussed_html .= <<<EOT
                    <tr>
                        <td class="text-truncate">
                            <a href="{$item_url}">{$item['message']}</a>
                        </td>
                    </tr>
EOT;
            }

            $discussed_html .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<b>ITEM DISCUSSIONS</b>', 'px-4 pt-3');
            $el->body($discussed_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Last projects discussed.
         */

        if ($this->app_settings->getUser('dashboard_remove_project_discussions') === '0') {

            $discussed_html = <<<EOT
                <table style="table-layout: fixed;width:100%;line-height: 2rem">
                    <tbody>
EOT;

            if (count($data['last_discussed_projects']) === 0) {

                $discussed_html .= <<<EOT
                    <tr>
                        <td style="height: 10rem" class="text-center text-secondary pb-4 align-middle">
                            NO POSTS
                        </td>
                    </tr>
EOT;
            }

            foreach ($data['last_discussed_projects'] as $project) {

                $item_url = IL_BASE_URL . 'index.php/project#project/discussion?id=' . $project['project_id'];

                $discussed_html .= <<<EOT
                    <tr>
                        <td class="text-truncate">
                            <a href="{$item_url}">{$project['message']}</a>
                        </td>
                    </tr>
EOT;
            }

            $discussed_html .= <<<EOT
                    </tbody>
                </table>
EOT;

            /** @var Bootstrap\Card $el */
            $el = $this->di->get('Card');

            $el->addClass('h-100');
            $el->header('<b>PROJECT DISCUSSIONS</b>', 'px-4 pt-3');
            $el->body($discussed_html, null, 'px-4 pb-4');
            $cards[] = $el->render();

            $el = null;
        }

        /*
         * Active sessions.
         */
        $this->temporal = $this->di->get('Temporal');

        $active_sessions = $this->session->readSessionFiles($data['sessions']);

        $sessions_card = '<div class="pl-4">LOGGED DEVICES</div>';

        foreach ($active_sessions as $active_session) {

            $created = $this->temporal->toUserTime($active_session['created']);
            $updated = $this->temporal->toUserTime($active_session['last_accessed']);

            $sessions_card .= <<<SESSION
<table class="ml-4 mb-2 text-muted">
    <tbody>
        <tr>
            <td class="align-top pr-3 text-primary">
                <small><b>CLIENT IP</b></small>
            </td>
            <td>
                {$active_session['remote_ip']}
            </td>
        </tr>
        <tr>
            <td class="align-top pr-3">
                <small><b>SOFTWARE</b></small>
            </td>
            <td>
                {$active_session['user_agent']}
            </td>
        </tr>
        <tr>
            <td class="align-top pr-3">
                <small><b>STARTED</b></small>
            </td>
            <td>
                $created
            </td>
        </tr>
        <tr>
            <td class="align-top pr-3">
                <small><b>LAST&nbsp;ACCESS</b></small>
            </td>
            <td>
                $updated
            </td>
        </tr>
    </tbody>
</table>
SESSION;

        }

        /*
         * Row.
         */

        /** @var Bootstrap\Row $el */
        $el = $this->di->get('Row');

        $el->addClass('d-flex align-content-start no-gutters');
        $el->column($bc, 'col-12');
        $el->column($cards[0] ?? '', 'col-xl-6 mb-3 pr-xl-2');
        $el->column($cards[1] ?? '', 'col-xl-6 mb-3 pl-xl-2');
        $el->column($cards[2] ?? '', 'col-xl-6 mb-3 pr-xl-2');
        $el->column($cards[3] ?? '', 'col-xl-6 mb-3 pl-xl-2');
        $el->column($cards[4] ?? '', 'col-xl-6 mb-3 pr-xl-2');
        $el->column($cards[5] ?? '', 'col-xl-6 mb-3 pl-xl-2');
        $el->column($cards[6] ?? '', 'col-xl-6 mb-3 pr-xl-2');
        $el->column($cards[7] ?? '', 'col-xl-6 mb-3 pl-xl-2');
        $el->column($sessions_card, 'col-12 mb-3');

        $content = $el->render();

        /*
         * Modals.
         */

        // Advanced search.

        /** @var Bootstrap\Button $el */
        $el = $this->di->get('Button');

        $el->addClass('search-submit');
        $el->context('primary');
        $el->html('Search');
        $search_button = $el->render();

        $el = null;

        /** @var Bootstrap\Modal $el */
        $el = $this->di->get('Modal');

        $el->id('modal-advanced-search');
        $el->header('Search library');
        $el->button($search_button);
        $el->body($this->sharedAdvancedSearch($data['tags']), 'bg-darker-5');
        $el->componentSize('large');
        $content .= $el->render();

        $el = null;

        $this->append([
            'html'       => $content,
            'id_list'    => $id_list,
            'activity'   => $data['activity'],
            'pages_read' => $data['pages_read']
        ]);

        return $this->send();
    }
}
