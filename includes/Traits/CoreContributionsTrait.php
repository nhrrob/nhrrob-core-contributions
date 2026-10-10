<?php

namespace Nhrcc\CoreContributions\Traits;

trait CoreContributionsTrait
{
    /**
     * Search the WordPress/wordpress-develop GitHub mirror for commits giving props to a username.
     * Core Trac search now sits behind a browser check that server-side requests cannot pass.
     *
     * @return array|false Decoded response, or false when GitHub gave no usable answer.
     */
    private function search_core_commits($username, $page = 1, $per_page = 10)
    {
        $url = add_query_arg(
            [
                'q'        => rawurlencode('repo:WordPress/wordpress-develop props ' . $username),
                'sort'     => 'committer-date',
                'order'    => 'desc',
                'per_page' => intval($per_page),
                'page'     => max(1, intval($page)),
            ],
            'https://api.github.com/search/commits'
        );

        $response = wp_remote_get($url, ['headers' => ['Accept' => 'application/vnd.github+json']]);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        return isset($data['total_count'], $data['items']) && is_array($data['items']) ? $data : false;
    }

    public function get_core_contributions($username, $page = 1, $cache_duration = '')
    {
        // Check for cached results
        $cache_key = 'nhrcc_' . md5($username . '_' . $page);
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }

        $username = sanitize_text_field($username);
        $page = intval( $page );

        $data = $this->search_core_commits($username, $page);

        if (empty($data['items'])) {
            return [];
        }

        $formatted = [];
        foreach ($data['items'] as $item) {
            $message = isset($item['commit']['message']) ? (string) $item['commit']['message'] : '';

            // Every commit in the mirror carries its Trac changeset number in the git-svn-id line.
            if ( ! preg_match( '/git-svn-id:\s*\S+@(\d+)/', $message, $changeset_match ) ) {
                continue;
            }

            $ticket = '';
            if ( preg_match( '/(?:fixes|ee) #(\d+)/i', $message, $ticket_match ) ) {
                $ticket = intval( $ticket_match[1] );
            }

            $timestamp = isset($item['commit']['committer']['date']) ? strtotime($item['commit']['committer']['date']) : false;

            $formatted[] = [
                'link'        => 'https://core.trac.wordpress.org/changeset/' . intval($changeset_match[1]),
                'changeset'   => intval($changeset_match[1]),
                'description' => sanitize_text_field( strtok($message, "\n") ),
                'ticket'      => $ticket,
                'date'        => $timestamp ? gmdate('m/d/Y h:i:s A', $timestamp) : '',
            ];
        }

        // Cache the results for 12 hours
        $nhrcc_settings = get_option('nhrcc_settings');
        
        $cache_duration_default = ! empty( $nhrcc_settings['cacheDuration'] ) ? intval( $nhrcc_settings['cacheDuration'] ) : 12 * HOUR_IN_SECONDS;
        $cache_duration = ! empty( $cache_duration ) ? $cache_duration : $cache_duration_default;

        set_transient($cache_key, $formatted, $cache_duration);
        // The same response carries the total, so the count needs no request of its own.
        set_transient('nhrcc_count_' . md5($username), intval($data['total_count']), 12 * HOUR_IN_SECONDS);

        return $formatted;
    }


    public function get_core_contribution_count($username)
    {
        // Check for cached count
        $cache_key = 'nhrcc_count_' . md5($username);
        $cached_count = get_transient($cache_key);
        if ($cached_count !== false) {
            // Transients stored in the options table come back as strings.
            return intval($cached_count);
        }

        $username = sanitize_text_field($username);
        $data = $this->search_core_commits($username, 1, 1);

        if (false === $data) {
            return '<p>' . __('Unable to fetch contributions at this time.', 'nhrrob-core-contributions') . '</p>';
        }

        $count = intval($data['total_count']);

        // Cache the count for 12 hours
        set_transient($cache_key, $count, 12 * HOUR_IN_SECONDS);

        return $count;
    }

    public function get_wporg_display_name( $username ) {
        // api link incorrect
        $username = sanitize_text_field( $username );
        $url = "https://profiles.wordpress.org/$username/profile.json";
    
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            return 'Unable to fetch user details.';
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (isset($data['name'])) {
            return $data['name']; // Display name
        }
    
        return 'Username not found or invalid.';
    }
}
