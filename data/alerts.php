<?php

/**
 * Provides localized alert data definitions.
 *
 * @package ProactiveSiteAdvisor\data
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

return [
    'severity'        => [
        'info'     => __(
            'Info',
            'proactive-site-advisor'
        ),
        'warning'  => __(
            'Warning',
            'proactive-site-advisor'
        ),
        'critical' => __(
            'Critical',
            'proactive-site-advisor'
        ),
    ],
    'severity_text'   => [
        'traffic_drop'  => [
            'info'     => __(
                'A small decrease in human traffic was detected compared to your recent activity. Small variations like this can happen naturally as visitor behavior changes.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Human traffic decreased noticeably compared to your recent activity. This type of change is worth reviewing because it may be related to recent updates, visibility changes, or visitor behavior changes.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Human traffic decreased significantly compared to your recent activity. This level of change is unusual and may indicate an issue affecting how visitors reach or interact with your site.',
                'proactive-site-advisor'
            ),
        ],
        'traffic_spike' => [
            'info'     => __(
                'A small increase in human traffic was detected compared to your recent activity. This may be a normal variation in visitor behavior or the result of recent content activity.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Human traffic increased noticeably compared to your recent activity. Reviewing recent content, campaigns, or changes can help identify what caused the increase.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Human traffic increased significantly compared to your recent activity. This unusual change is worth reviewing to understand whether it came from content activity, campaigns, visibility changes, or unexpected visitor behavior.',
                'proactive-site-advisor'
            ),
        ],
        '404_spike'     => [
            'info'     => __(
                'A small increase in 404 errors was detected compared to your recent activity. Minor changes like this can happen naturally as visitors reach older or unavailable pages.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                '404 errors increased noticeably compared to your recent activity. Reviewing the affected URLs can help identify whether recent changes or broken links caused the increase.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                '404 errors increased significantly compared to your recent activity. This unusual change may indicate that visitors are reaching missing pages more often, so the affected URLs should be reviewed to find the cause.',
                'proactive-site-advisor'
            ),
        ],
        'bot_spike'     => [
            'info'     => __(
                'A small increase in bot activity was detected compared to your recent pattern. This may be a normal variation in automated visits.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Bot activity increased noticeably compared to your recent pattern. Reviewing the detected bots can help determine whether this change is expected.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Bot activity increased significantly compared to your recent pattern. Review the detected bots and their request volume to understand whether this activity may affect your site resources.',
                'proactive-site-advisor'
            ),
        ],
        'bot_drop'      => [
            'info'     => __(
                'A small decrease in bot activity was detected compared to your recent pattern. Small variations in automated activity can happen naturally.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Bot activity decreased noticeably compared to your recent pattern. Reviewing recent site changes can help identify what caused the change.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Bot activity decreased significantly compared to your recent pattern. Review crawler access and recent site changes to understand why automated activity has changed.',
                'proactive-site-advisor'
            ),
        ],
    ],
    'common'          => [
        'repetition_second_day'   => __('This alert has appeared for the second consecutive day.', 'proactive-site-advisor'),
        'repetition_trend'        => __('This alert has continued for multiple consecutive days and may indicate a developing trend.', 'proactive-site-advisor'),
        /* translators: %s: List of concurrent alert types */
        'concurrent_with'         => __('Detected together with: %s.', 'proactive-site-advisor'),
        'check_fix_broken_links'  => __('Review broken links first, as they may be affecting traffic.', 'proactive-site-advisor'),
        'check_automated_traffic' => __('Review whether automated traffic is contributing to the increase.', 'proactive-site-advisor'),
        'pattern_continue'        => __('Review whether this pattern continues over time.', 'proactive-site-advisor'),
    ],
    'badge_labels'    => [
        'traffic_drop'  => __('Traffic', 'proactive-site-advisor'),
        'traffic_spike' => __('Traffic', 'proactive-site-advisor'),
        '404_spike'     => __('404 Errors', 'proactive-site-advisor'),
        'bot_spike'     => __('Bot Activity', 'proactive-site-advisor'),
        'bot_drop'      => __('Bot Activity', 'proactive-site-advisor'),
    ],
    'title_templates' => [
        /* translators: %s: Percentage change value */
        'traffic_drop'  => __('Traffic dropped %s%%', 'proactive-site-advisor'),
        /* translators: %s: Percentage change value */
        'traffic_spike' => __('Traffic surged %s%%', 'proactive-site-advisor'),
        /* translators: %s: Percentage change value */
        '404_spike'     => __('404 errors surged %s%%', 'proactive-site-advisor'),
        /* translators: %s: Percentage change value */
        'bot_spike'     => __('Bot activity surged %s%%', 'proactive-site-advisor'),
        /* translators: %s: Percentage change value */
        'bot_drop'      => __('Bot activity dropped %s%%', 'proactive-site-advisor'),
    ],
    'traffic_drop'    => [
        'label'   => __(
            'Traffic Drop',
            'proactive-site-advisor'
        ),
        'short'   => [
            'info'     => __(
                'Your site received slightly fewer human visitors than usual.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Your site received fewer human visitors than your recent average.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Your human traffic decreased sharply compared to recent activity.',
                'proactive-site-advisor'
            ),
        ],
        'context' => __(
            'A decrease in human traffic means fewer real visitors reached your site compared to your normal activity. This does not always indicate a problem and can happen after website changes, availability issues, visibility changes, broken links, or changes in visitor behavior.',
            'proactive-site-advisor'
        ),
        'checks'  => [
            'base'     => [
                __(
                    'Check that your site is loading normally and important pages are accessible.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review recent changes, including plugin updates, theme changes, migrations, or new content edits.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check whether broken links or increased 404 errors are preventing visitors from reaching important pages.',
                    'proactive-site-advisor'
                ),
            ],
            'warning'  => [
                __(
                    'Compare when the traffic change started with recent updates or configuration changes to help identify possible causes.',
                    'proactive-site-advisor'
                ),
            ],
            'critical' => [
                __(
                    'Verify that your most important pages are available and responding correctly.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review major recent changes, migrations, deployments, or settings updates that may have affected visitor access.',
                    'proactive-site-advisor'
                ),
            ],
        ],
    ],
    'traffic_spike'   => [
        'label'   => __(
            'Traffic Spike',
            'proactive-site-advisor'
        ),
        'short'   => [
            'info'     => __(
                'Human traffic is slightly higher than your normal activity.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Human traffic is higher than your recent average.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Human traffic increased sharply compared to recent activity.',
                'proactive-site-advisor'
            ),
        ],
        'context' => __(
            'An increase in human traffic can be a positive sign, such as popular content, campaigns, or improved visibility. Unusual increases may indicate changes in visitor behavior or activity that is worth reviewing.',
            'proactive-site-advisor'
        ),
        'checks'  => [
            'base'     => [
                __(
                    'Review which pages are receiving the additional visitors.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check recent content updates, campaigns, or promotions that may explain the increase.',
                    'proactive-site-advisor'
                ),
                __(
                    'Monitor site performance to make sure your hosting can handle the additional traffic.',
                    'proactive-site-advisor'
                ),
            ],
            'warning'  => [
                __(
                    'Review where the additional visitors are coming from and whether the increase matches your expectations.',
                    'proactive-site-advisor'
                ),
            ],
            'critical' => [
                __(
                    'Review the source and timing of the increase to understand what caused this unusual change.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review server resources and availability during periods of higher traffic.',
                    'proactive-site-advisor'
                ),
            ],
        ],
    ],
    '404_spike'       => [
        'label'   => __(
            '404 Spike',
            'proactive-site-advisor'
        ),
        'short'   => [
            'info'     => __(
                'Your site is receiving slightly more 404 errors than usual.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Your site is receiving more 404 errors than your recent average.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                '404 errors increased sharply, meaning more visitors are reaching pages that no longer exist.',
                'proactive-site-advisor'
            ),
        ],
        'context' => __(
            '404 errors occur when visitors reach pages that no longer exist on your site. This can happen after URL changes, deleted content, broken internal links, or changes to your site structure.',
            'proactive-site-advisor'
        ),
        'checks'  => [
            'base'     => [
                __(
                    'Review the most frequently requested missing URLs shown below.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check whether these URLs should redirect to existing content.',
                    'proactive-site-advisor'
                ),
                __(
                    'Update internal links that point visitors to missing pages.',
                    'proactive-site-advisor'
                ),
            ],
            'warning'  => [
                __(
                    'Review recent content changes, permalink updates, or site structure changes that may have caused the increase.',
                    'proactive-site-advisor'
                ),
            ],
            'critical' => [
                __(
                    'Review the missing URLs receiving the most requests and determine whether they need redirects or other fixes.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check whether a recent migration, update, or configuration change caused the increase.',
                    'proactive-site-advisor'
                ),
            ],
        ],
    ],
    'bot_spike'       => [
        'label'   => __(
            'Bot Spike',
            'proactive-site-advisor'
        ),
        'short'   => [
            'info'     => __(
                'Bot activity is slightly higher than your recent activity.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Bot activity is higher than your recent average.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Bot activity increased sharply and may affect your site resources or performance.',
                'proactive-site-advisor'
            ),
        ],
        'context' => __(
            'An increase in bot activity means more automated systems are accessing your site than usual. This can happen because of search crawlers, SEO tools, monitoring services, or other automated services. Reviewing the detected bots can help you understand the reason for the change.',
            'proactive-site-advisor'
        ),
        'checks'  => [
            'base'     => [
                __(
                    'Review the detected bots and their request volume shown below.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check whether the increased activity is affecting your site performance.',
                    'proactive-site-advisor'
                ),
                __(
                    'Verify that expected crawlers can still access your site normally.',
                    'proactive-site-advisor'
                ),
            ],
            'warning'  => [
                __(
                    'Review which bots caused the increase and whether their activity matches your expectations.',
                    'proactive-site-advisor'
                ),
            ],
            'critical' => [
                __(
                    'Review whether the detected bots are consuming significant site resources.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review server performance and security logs if the activity appears unexpected.',
                    'proactive-site-advisor'
                ),
            ],
        ],
    ],
    'bot_drop'        => [
        'label'   => __(
            'Bot Activity Drop',
            'proactive-site-advisor'
        ),
        'short'   => [
            'info'     => __(
                'Bot activity is slightly lower than your recent activity.',
                'proactive-site-advisor'
            ),
            'warning'  => __(
                'Bot activity is lower than your recent average.',
                'proactive-site-advisor'
            ),
            'critical' => __(
                'Bot activity dropped sharply and may indicate changes in crawler access or how automated systems reach your site.',
                'proactive-site-advisor'
            ),
        ],
        'context' => __(
            'A decrease in bot activity means fewer automated systems are accessing your site compared to your usual pattern. This can happen because of crawl changes, accessibility issues, robots.txt changes, sitemap problems, or other factors that affect how bots reach your site.',
            'proactive-site-advisor'
        ),
        'checks'  => [
            'base'     => [
                __(
                    'Check that your site is accessible to search engine crawlers.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review recent changes to robots.txt, sitemap, or SEO settings.',
                    'proactive-site-advisor'
                ),
                __(
                    'Check if key pages are still available and returning normal responses.',
                    'proactive-site-advisor'
                ),
            ],
            'warning'  => [
                __(
                    'Review whether recent updates or site changes affected crawler access.',
                    'proactive-site-advisor'
                ),
            ],
            'critical' => [
                __(
                    'Verify that search engine crawlers can still access the pages they need to reach.',
                    'proactive-site-advisor'
                ),
                __(
                    'Review whether crawler access or search visibility may be affected.',
                    'proactive-site-advisor'
                ),
            ],
        ],
    ],
];