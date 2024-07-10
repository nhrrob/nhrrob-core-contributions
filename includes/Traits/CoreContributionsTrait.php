<?php

namespace Nhrcc\CoreContributions\Traits;

trait CoreContributionsTrait
{
    public function get_core_contributions($username, $page = 1)
    {
        // Check for cached results
        $cache_key = 'nhrcc_' . md5($username . '_' . $page);
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }

        $url = "https://core.trac.wordpress.org/search?q=props+$username&noquickjump=1&changeset=on&page=$page";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return [];
        }

        // Parse HTML to extract the relevant data
        $pattern = '/<dt><a href="(.*?)" class="searchable">\[(.*?)\]: ((?s).*?)<\/a><\/dt>\n\s*(<dd class="searchable">.*\n?.*(?:ixes|ee) #(.*?)\n?<\/dd>)?/';
        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return [];
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


    public function get_core_contribution_count($username)
    {
        // Check for cached count
        $cache_key = 'nhrcc_count_' . md5($username);
        $cached_count = get_transient($cache_key);
        if ($cached_count !== false) {
            return $cached_count;
        }

        $url = "https://core.trac.wordpress.org/search?q=props+$username&noquickjump=1&changeset=on";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return '<p>' . __('Unable to fetch contributions at this time.', 'nhrrob-core-contributions') . '</p>';
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return '<p>' . __('No contributions found for this user.', 'nhrrob-core-contributions') . '</p>';
        }

        // Parse HTML to extract the total number of results
        $pattern = '/<meta name="totalResults" content="(\d*)" \/>/';
        preg_match($pattern, $body, $matches);

        if (!isset($matches[1])) {
            return '<p>' . __('No contributions found for this user.', 'nhrrob-core-contributions') . '</p>';
        }

        $count = intval($matches[1]);

        // Cache the count for 12 hours
        set_transient($cache_key, $count, 12 * HOUR_IN_SECONDS);

        return $count;
    }
}
