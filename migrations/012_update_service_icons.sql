-- Обновление иконок для services (совместимость с MySQL 5.5+)
-- Добавление уникальных иконок для каждой услуги

UPDATE services SET icon = 'fa-file-lines' WHERE id = 'copy_bw_a4';
UPDATE services SET icon = 'fa-file-invoice' WHERE id = 'copy_bw_a3';
UPDATE services SET icon = 'fa-palette' WHERE id = 'copy_color_a4';
UPDATE services SET icon = 'fa-paint-brush' WHERE id = 'copy_color_a3';
UPDATE services SET icon = 'fa-id-badge' WHERE id = 'copy_passport';

UPDATE services SET icon = 'fa-file-pdf' WHERE id = 'print_bw_a4';
UPDATE services SET icon = 'fa-file-contract' WHERE id = 'print_bw_a3';
UPDATE services SET icon = 'fa-image' WHERE id = 'print_color_a4';
UPDATE services SET icon = 'fa-images' WHERE id = 'print_color_a3';

UPDATE services SET icon = 'fa-scanner-image' WHERE id = 'scan_a4';
UPDATE services SET icon = 'fa-file-zipper' WHERE id = 'scan_a3';
UPDATE services SET icon = 'fa-spell-check' WHERE id = 'scan_ocr';
UPDATE services SET icon = 'fa-book-open' WHERE id = 'scan_book';

UPDATE services SET icon = 'fa-address-card' WHERE id = 'business_cards_std_1side';
UPDATE services SET icon = 'fa-id-card-clip' WHERE id = 'business_cards_std_2side';
UPDATE services SET icon = 'fa-layer-group' WHERE id = 'business_cards_laminated';
UPDATE services SET icon = 'fa-crown' WHERE id = 'business_cards_designer';

UPDATE services SET icon = 'fa-file-alt' WHERE id = 'flyers_a6';
UPDATE services SET icon = 'fa-file-invoice' WHERE id = 'flyers_a5';
UPDATE services SET icon = 'fa-file-lines' WHERE id = 'flyers_a4';

UPDATE services SET icon = 'fa-book' WHERE id = 'brochure_a5_8p_staple';
UPDATE services SET icon = 'fa-book-open' WHERE id = 'brochure_a5_16p_staple';
UPDATE services SET icon = 'fa-book-bookmark' WHERE id = 'brochure_a5_24p_staple';
UPDATE services SET icon = 'fa-book-journal-whills' WHERE id = 'brochure_a4_20p_spiral';
UPDATE services SET icon = 'fa-books' WHERE id = 'brochure_a4_50p_spiral';
UPDATE services SET icon = 'fa-book-atlas' WHERE id = 'brochure_a4_100p_spiral';

UPDATE services SET icon = 'fa-rectangle-ad' WHERE id = 'poster_a3';
UPDATE services SET icon = 'fa-panorama' WHERE id = 'poster_a2';
UPDATE services SET icon = 'fa-image-portrait' WHERE id = 'poster_a1';
UPDATE services SET icon = 'fa-picture-o' WHERE id = 'poster_a0';

UPDATE services SET icon = 'fa-sticker-mule' WHERE id = 'stickers_paper_50x50';
UPDATE services SET icon = 'fa-tag' WHERE id = 'stickers_paper_100x100';
UPDATE services SET icon = 'fa-certificate' WHERE id = 'stickers_vinyl_50x50';
UPDATE services SET icon = 'fa-award' WHERE id = 'stickers_vinyl_100x100';
UPDATE services SET icon = 'fa-tags' WHERE id = 'sticker_packs';

UPDATE services SET icon = 'fa-flag' WHERE id = 'banner_1x1';
UPDATE services SET icon = 'fa-flag-checkered' WHERE id = 'banner_2x1';
UPDATE services SET icon = 'fa-flag-pennant' WHERE id = 'banner_3x2';
UPDATE services SET icon = 'fa-flag-swallowtail' WHERE id = 'banner_6x3';
UPDATE services SET icon = 'fa-display' WHERE id = 'rollup_08x2';
UPDATE services SET icon = 'fa-chalkboard' WHERE id = 'rollup_1x2';
UPDATE services SET icon = 'fa-border-all' WHERE id = 'wallpaper_photo';

UPDATE services SET icon = 'fa-bag-shopping' WHERE id = 'paper_bag_s';
UPDATE services SET icon = 'fa-shopping-bag' WHERE id = 'paper_bag_m';
UPDATE services SET icon = 'fa-suitcase' WHERE id = 'paper_bag_l';
UPDATE services SET icon = 'fa-box-archive' WHERE id = 'cardboard_box_s';
UPDATE services SET icon = 'fa-boxes-stacked' WHERE id = 'cardboard_box_m';

UPDATE services SET icon = 'fa-shirt' WHERE id = 'tshirt_print';
UPDATE services SET icon = 'fa-mug-hot' WHERE id = 'mug_photo';
UPDATE services SET icon = 'fa-circle-dot' WHERE id = 'badge_56mm';
UPDATE services SET icon = 'fa-magnet' WHERE id = 'fridge_magnet';
UPDATE services SET icon = 'fa-bag-shopping' WHERE id = 'eco_shopper';

UPDATE services SET icon = 'fa-image' WHERE id = 'canvas_30x40';
UPDATE services SET icon = 'fa-photo-film' WHERE id = 'canvas_50x70';
UPDATE services SET icon = 'fa-panorama' WHERE id = 'canvas_70x100';
UPDATE services SET icon = 'fa-border-none' WHERE id = 'modular_90x60';
UPDATE services SET icon = 'fa-grip-horizontal' WHERE id = 'modular_120x80';
UPDATE services SET icon = 'fa-grip-vertical' WHERE id = 'modular_150x100';

UPDATE services SET icon = 'fa-stapler' WHERE id = 'staple_binding';
UPDATE services SET icon = 'fa-book-open' WHERE id = 'spiral_binding';
UPDATE services SET icon = 'fa-book-bible' WHERE id = 'thermal_binding';
UPDATE services SET icon = 'fa-shield-halved' WHERE id = 'lamination_a4';
UPDATE services SET icon = 'fa-shield' WHERE id = 'lamination_a3';
UPDATE services SET icon = 'fa-scissors' WHERE id = 'cutting';
UPDATE services SET icon = 'fa-circle-notch' WHERE id = 'perforation';

UPDATE services SET icon = 'fa-palette' WHERE id = 'design_business_card';
UPDATE services SET icon = 'fa-brush' WHERE id = 'design_flyer';
UPDATE services SET icon = 'fa-paint-roller' WHERE id = 'design_booklet';
UPDATE services SET icon = 'fa-stamp' WHERE id = 'design_logo';
UPDATE services SET icon = 'fa-briefcase' WHERE id = 'design_branding';
UPDATE services SET icon = 'fa-file-code' WHERE id = 'layout_service';
UPDATE services SET icon = 'fa-gears' WHERE id = 'prepress_service';

UPDATE services SET icon = 'fa-truck-fast' WHERE id = 'delivery_moscow_10km';
UPDATE services SET icon = 'fa-truck' WHERE id = 'delivery_mo_30km';
UPDATE services SET icon = 'fa-keyboard' WHERE id = 'typing_service';
UPDATE services SET icon = 'fa-text-height' WHERE id = 'ocr_text_recognition';
