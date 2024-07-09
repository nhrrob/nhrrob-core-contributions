<?php

namespace Nhrcc\CoreContributions\Admin;


/**
 * The Menu handler class
 */
class SettingsPage extends Page
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handles the settings page
     *
     * @return void
     */
    public function view()
    {
        global $wpdb;

        $core_contributions = $this->get_core_contributions('robinwpdeveloper');
        // $this->get_core_contributions('robinwpdeveloper');

        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        echo wp_kses($content, $this->allowed_html());
    }

    public function get_core_contributions($username)
    {
        // Check for cached results
        $cache_key = 'nhrcc_' . md5($username);
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }

        $url = "https://core.trac.wordpress.org/search?q=$username&noquickjump=1&changeset=on";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return '<p>' . __('Unable to fetch contributions at this time.', 'nhrrob-core-contributions') . '</p>';
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return '<p>' . __('No contributions found for this user.', 'nhrrob-core-contributions') . '</p>';
        }

        // Parse HTML to extract the relevant data
        $pattern = '/<dt><a href="(.*?)" class="searchable">\[(.*?)\]: ((?s).*?)<\/a><\/dt>\n\s*(<dd class="searchable">.*\n?.*(?:ixes|ee) #(.*?)\n?<\/dd>)?/';
        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return '<p>' . __('No contributions found for this user.', 'nhrrob-core-contributions') . '</p>';
        }

        $formatted = [];
        foreach ($matches as $match) {
            $formatted[] = [
                'link'        => 'https://core.trac.wordpress.org' . $match[1],
                'changeset'   => intval($match[2]),
                'description' => $match[3],
                'ticket'      => isset($match[5]) ? intval($match[5]) : '',
            ];
        }

        // Cache the results for 12 hours
        set_transient($cache_key, $formatted, 12 * HOUR_IN_SECONDS);

        return $formatted;
    }
}
