<?php
/**
 * One-off: create Diensten parent page, nest service children, update homepage cards.
 * Run: wp eval-file backups/setup-diensten-page.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$siteUrl = untrailingslashit(home_url());

$childIds = [
    'website-laten-maken' => 729,
    'gezondheidscheck' => 730,
    'ai-workflows' => 731,
];

$childUrls = [
    'website' => $siteUrl . '/diensten/website-laten-maken/',
    'health' => $siteUrl . '/diensten/gezondheidscheck/',
    'ai' => $siteUrl . '/diensten/ai-workflows/',
];

$existing = get_page_by_path('diensten');
if ($existing) {
    $dienstenId = (int) $existing->ID;
    WP_CLI::log("Diensten page already exists (ID {$dienstenId}), updating…");
} else {
    $dienstenId = wp_insert_post([
        'post_title' => 'Diensten',
        'post_name' => 'diensten',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1,
        'post_parent' => 0,
    ], true);

    if (is_wp_error($dienstenId)) {
        WP_CLI::error($dienstenId->get_error_message());
    }

    WP_CLI::success("Created Diensten page (ID {$dienstenId})");
}

$cardsBlock = '<!-- wp:acf/section-section-cards {"name":"acf/section-section-cards","data":{"section_title":"","_section_title":"field_6c8d9e0f1a2b4","title_heading_tag":"h2","_title_heading_tag":"field_6c8d9e0f1a2b5","title_heading_size":"h2","_title_heading_size":"field_6a4d0a4b3e5f6","cards_per_row":"3","_cards_per_row":"field_6c8d9e0f1a2b6","cards_0_card_title":"Nieuwe website laten maken","_cards_0_card_title":"field_6c8d9e0f1a2b8","cards_0_card_content":"Vergeet trage, saaie templates. TechnoSapiens bouwt een veilig en krachtig platform op maat van jouw merk. Volledig afgestemd met de nieuwste technologische trends.","_cards_0_card_content":"field_6c8d9e0f1a2b9","cards_0_card_link":{"title":"Meer over websites","url":"' . $childUrls['website'] . '","target":""},"_cards_0_card_link":"field_6c8d9e0f1a2ba","cards_1_card_title":"Gezondheidscheck van jouw website","_cards_1_card_title":"field_6c8d9e0f1a2b8","cards_1_card_content":"Niet tevreden over de prestaties van jouw website? TechnoSapiens helpt je weer vooruit! Door middel van een solide technische- en security-audit adviseert TechnoSapiens hoe jouw platform veilig, schaalbaar en toekomstbestendig is.","_cards_1_card_content":"field_6c8d9e0f1a2b9","cards_1_card_link":{"title":"Meer over de check","url":"' . $childUrls['health'] . '","target":""},"_cards_1_card_link":"field_6c8d9e0f1a2ba","cards_2_card_title":"Integraties en AI","_cards_2_card_title":"field_6c8d9e0f1a2b8","cards_2_card_content":"Heb je een complex vraagstuk? TechnoSapiens integreert moeiteloos API\'s en bouwt slimme AI-workflows. Zo versnelt jouw bedrijfsproces en groeit je organisatie direct mee.","_cards_2_card_content":"field_6c8d9e0f1a2b9","cards_2_card_link":{"title":"Meer over AI workflows","url":"' . $childUrls['ai'] . '","target":""},"_cards_2_card_link":"field_6c8d9e0f1a2ba","cards":3,"_cards":"field_6c8d9e0f1a2b7","section_margin_top":"mt-xl","_section_margin_top":"field_6a4a4ab0b6dc0","section_margin_bottom":"mb-lg","_section_margin_bottom":"field_6156ab4c687e8","section_vignet_top":"1","_section_vignet_top":"field_6aaeef9d735d6","section_vignet_bottom":"1","_section_vignet_bottom":"field_6aaeefd2735d7","background_type":"bands","_background_type":"field_6aaef7702bdba","background_bands_axis":"horizontal","_background_bands_axis":"field_6aaef7c42bdbb","background_bands_size":"large","_background_bands_size":"field_6aaef84e3c58f","background_bands_anim_dir":"top-bottom","_background_bands_anim_dir":"field_6aaef8153c58e"},"mode":"preview"} /-->';

$content = <<<CONTENT
<!-- wp:acf/section-section-heading {"name":"acf/section-section-heading","data":{"title_text":"Techniek met \u003cspan class=\u0022text-color-secondary\u0022\u003emenselijke regie\u003c/span\u003e","_title_text":"field_6aaee8e9340f4","title_heading_tag":"h2","_title_heading_tag":"field_6aaeea7128b01","title_heading_size":"h2","_title_heading_size":"field_6ac6297dec0ff","section_margin_top":"mt-0","_section_margin_top":"field_6a4a4ab0b6dc0","section_margin_bottom":"mb-m","_section_margin_bottom":"field_6156ab4c687e8","section_vignet_top":"0","_section_vignet_top":"field_6aaeef9d735d6","section_vignet_bottom":"0","_section_vignet_bottom":"field_6aaeefd2735d7","background_type":"","_background_type":"field_6aaef7702bdba"}} /-->

<!-- wp:acf/section-section-content {"name":"acf/section-section-content","data":{"section_title":"Wat TechnoSapiens voor je kan betekenen","_section_title":"field_6a4ac67bc9b70","title_heading_tag":"h2","_title_heading_tag":"field_6a4ac67bc9b71","title_heading_size":"h2","_title_heading_size":"field_6a4ac67bc9b72","content_text":"\u003cp\u003eTechnoSapiens helpt ondernemers met \u003cstrong\u003edigitale diensten\u003c/strong\u003e die snel, veilig en schaalbaar zijn — zonder anonieme systemen of eindeloze agencies. Je krijgt één vast aanspreekpunt dat meedenkt over strategie, techniek en AI.\u003c/p\u003e\u003cp\u003eOf je nu een \u003ca href=\u0022{$childUrls['website']}\u0022\u003ewebsite wilt laten maken\u003c/a\u003e, een \u003ca href=\u0022{$childUrls['health']}\u0022\u003egezondheidscheck\u003c/a\u003e nodig hebt, of \u003ca href=\u0022{$childUrls['ai']}\u0022\u003eAI workflows\u003c/a\u003e wilt inrichten: hier vind je de diensten die passen bij jouw fase.\u003c/p\u003e","_content_text":"field_6a4ac67bc9b6f","section_margin_top":"mt-0","_section_margin_top":"field_6a4a4ab0b6dc0","section_margin_bottom":"mb-xl","_section_margin_bottom":"field_6156ab4c687e8","section_vignet_top":"0","_section_vignet_top":"field_6aaeef9d735d6","section_vignet_bottom":"1","_section_vignet_bottom":"field_6aaeefd2735d7","background_type":"","_background_type":"field_6aaef7702bdba"}} /-->

<!-- wp:acf/section-section-heading {"name":"acf/section-section-heading","data":{"title_text":"Onze \u003cspan class=\u0022text-color-secondary\u0022\u003ediensten\u003c/span\u003e","_title_text":"field_6aaee8e9340f4","title_heading_tag":"h2","_title_heading_tag":"field_6aaeea7128b01","title_heading_size":"h2","_title_heading_size":"field_6ac6297dec0ff","section_margin_top":"","_section_margin_top":"field_6a4a4ab0b6dc0","section_margin_bottom":"","_section_margin_bottom":"field_6156ab4c687e8","section_vignet_top":"0","_section_vignet_top":"field_6aaeef9d735d6","section_vignet_bottom":"0","_section_vignet_bottom":"field_6aaeefd2735d7","background_type":"solid","_background_type":"field_6aaef7702bdba","background_color":"dark","_background_color":"field_6ac62c5c21111"}} /-->

{$cardsBlock}

<!-- wp:acf/section-section-content-banner {"name":"acf/section-section-content-banner","data":{"section_title":"Klaar om te sparren?","_section_title":"field_8d1c24cb8cbb","title_heading_tag":"h2","_title_heading_tag":"field_5d398c444391","title_heading_size":"h2","_title_heading_size":"field_6a4c9f3a2d4e5","content_text":"\u003cp\u003eVertel waar je tegenaan loopt. We kijken samen welke dienst het beste past — en wat de slimste volgende stap is.\u003c/p\u003e","_content_text":"field_da4c0e9de675","content_link":{"title":"Laten we kennismaken","url":"{$siteUrl}/contact/?topic=meet","target":""},"_content_link":"field_852ce23f722b","section_margin_top":"mt-xl","_section_margin_top":"field_6a4a4ab0b6dc0","section_margin_bottom":"mb-xl","_section_margin_bottom":"field_6156ab4c687e8","section_vignet_top":"1","_section_vignet_top":"field_6aaeef9d735d6","section_vignet_bottom":"1","_section_vignet_bottom":"field_6aaeefd2735d7","background_type":"bands","_background_type":"field_6aaef7702bdba","background_bands_axis":"vertical","_background_bands_axis":"field_6aaef7c42bdbb","background_bands_size":"medium","_background_bands_size":"field_6aaef84e3c58f","background_bands_anim_dir":"center","_background_bands_anim_dir":"field_6aaef8153c58e"},"mode":"preview"} /-->
CONTENT;

$update = wp_update_post([
    'ID' => $dienstenId,
    'post_content' => $content,
], true);

if (is_wp_error($update)) {
    WP_CLI::error($update->get_error_message());
}

$heroMeta = [
    'hm_size' => 'regular',
    'hm_heading_text' => 'Diensten van TechnoSapiens',
    'hm_heading_tag' => 'h1',
    'hm_sub_heading' => 'Website, gezondheidscheck en AI workflows',
    'hm_content' => 'Digitale diensten met moderne techniek én een vast menselijk aanspreekpunt. Kies wat je nodig hebt — of spar eerst samen over de juiste route.',
    'hm_slot_content' => 'breadcrumbs',
    'hm_button_size' => 'large',
    'hm_disable_automatic_heading' => 0,
    'hm_vignet_bottom' => 0,
    'hm_buttons' => 2,
    'hm_buttons_0_hm_button_type' => 'primary',
    'hm_buttons_0_hm_button_style' => 'filled',
    'hm_buttons_0_hm_button_link' => [
        'title' => 'Laten we kennismaken',
        'url' => $siteUrl . '/contact/?topic=meet',
        'target' => '',
    ],
    'hm_buttons_1_hm_button_type' => 'secondary',
    'hm_buttons_1_hm_button_style' => 'filled',
    'hm_buttons_1_hm_button_link' => [
        'title' => 'Bekijk cases',
        'url' => $siteUrl . '/cases/',
        'target' => '',
    ],
];

foreach ($heroMeta as $key => $value) {
    update_field($key, $value, $dienstenId);
}

$yoast = [
    '_yoast_wpseo_focuskw' => 'diensten TechnoSapiens',
    '_yoast_wpseo_title' => 'Diensten | TechnoSapiens',
    '_yoast_wpseo_metadesc' => 'Ontdek de diensten van TechnoSapiens: website laten maken, gezondheidscheck en AI workflows. Techniek met menselijke regie.',
    '_yoast_wpseo_opengraph-title' => 'Diensten | TechnoSapiens',
    '_yoast_wpseo_opengraph-description' => 'Ontdek de diensten van TechnoSapiens: website laten maken, gezondheidscheck en AI workflows. Techniek met menselijke regie.',
    '_yoast_wpseo_twitter-title' => 'Diensten | TechnoSapiens',
    '_yoast_wpseo_twitter-description' => 'Ontdek de diensten van TechnoSapiens: website laten maken, gezondheidscheck en AI workflows. Techniek met menselijke regie.',
    '_yoast_wpseo_opengraph-image' => $siteUrl . '/wp-content/uploads/2026/10/og-technosapiens-1200x630-1.png',
    '_yoast_wpseo_twitter-image' => $siteUrl . '/wp-content/uploads/2026/10/og-technosapiens-1200x630-1.png',
];

foreach ($yoast as $key => $value) {
    update_post_meta($dienstenId, $key, $value);
}

WP_CLI::success('Updated Diensten content, hero and Yoast SEO');

$oldUrls = [];
foreach ($childIds as $slug => $childId) {
    $oldUrls[$childId] = get_permalink($childId);
    $result = wp_update_post([
        'ID' => $childId,
        'post_parent' => $dienstenId,
    ], true);

    if (is_wp_error($result)) {
        WP_CLI::warning("Could not nest {$slug}: " . $result->get_error_message());
        continue;
    }

    clean_post_cache($childId);
    WP_CLI::log("Nested {$slug} (ID {$childId}) under Diensten → " . get_permalink($childId));
}

clean_post_cache($dienstenId);
flush_rewrite_rules(false);

$home = get_post(2);
if ($home) {
    $homeContent = $home->post_content;
    $replacements = [
        $siteUrl . '/website-laten-maken/' => $childUrls['website'],
        $siteUrl . '/gezondheidscheck/' => $childUrls['health'],
        $siteUrl . '/ai-workflows/' => $childUrls['ai'],
    ];

    $updatedHome = str_replace(array_keys($replacements), array_values($replacements), $homeContent);
    if ($updatedHome !== $homeContent) {
        wp_update_post([
            'ID' => 2,
            'post_content' => $updatedHome,
        ]);
        WP_CLI::success('Updated homepage card URLs to /diensten/… children');
    } else {
        WP_CLI::log('Homepage card URLs already pointed to /diensten/… or pattern not found');
    }
}

$menus = ['primair-menu', 'mobiel-menu', 'footer-menu'];
foreach ($menus as $menuSlug) {
    $menu = wp_get_nav_menu_object($menuSlug);
    if (!$menu) {
        continue;
    }

    $items = wp_get_nav_menu_items($menu->term_id);
    $already = false;
    foreach ($items ?: [] as $item) {
        if ((int) $item->object_id === $dienstenId) {
            $already = true;
            break;
        }
    }

    if ($already) {
        WP_CLI::log("Menu {$menuSlug}: Diensten already present");
        continue;
    }

    $aboutItem = null;
    foreach ($items ?: [] as $item) {
        if ((int) $item->object_id === 130) {
            $aboutItem = $item;
            break;
        }
    }

    $position = $aboutItem ? ((int) $aboutItem->menu_order) : 2;
    $itemId = wp_update_nav_menu_item($menu->term_id, 0, [
        'menu-item-title' => 'Diensten',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $dienstenId,
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish',
        'menu-item-position' => $position,
    ]);

    if (is_wp_error($itemId)) {
        WP_CLI::warning("Menu {$menuSlug}: " . $itemId->get_error_message());
    } else {
        WP_CLI::success("Added Diensten to {$menuSlug} (item {$itemId})");
    }
}

if (!is_plugin_active('redirection/redirection.php')) {
    $activated = activate_plugin('redirection/redirection.php');
    if (is_wp_error($activated)) {
        WP_CLI::warning('Could not activate Redirection: ' . $activated->get_error_message());
    } else {
        WP_CLI::log('Activated Redirection plugin for 301s');
    }
}

if (class_exists('Red_Item')) {
    $redirectMap = [
        '/website-laten-maken/' => '/diensten/website-laten-maken/',
        '/gezondheidscheck/' => '/diensten/gezondheidscheck/',
        '/ai-workflows/' => '/diensten/ai-workflows/',
    ];

    foreach ($redirectMap as $from => $to) {
        $existingRedirects = Red_Item::get_for_url($from);
        if (!empty($existingRedirects)) {
            WP_CLI::log("Redirect already exists for {$from}");
            continue;
        }

        $created = Red_Item::create([
            'url' => $from,
            'action_data' => ['url' => $to],
            'match_type' => 'url',
            'action_type' => 'url',
            'action_code' => 301,
            'group_id' => 1,
            'status' => 'enabled',
        ]);

        if (is_wp_error($created)) {
            WP_CLI::warning("Redirect {$from}: " . $created->get_error_message());
        } else {
            WP_CLI::success("301 {$from} → {$to}");
        }
    }
} else {
    WP_CLI::warning('Redirection API unavailable; add 301s manually for old service URLs');
}

WP_CLI::success('Done. Diensten: ' . get_permalink($dienstenId));
foreach ($childIds as $slug => $childId) {
    WP_CLI::log("  - {$slug}: " . get_permalink($childId));
}
