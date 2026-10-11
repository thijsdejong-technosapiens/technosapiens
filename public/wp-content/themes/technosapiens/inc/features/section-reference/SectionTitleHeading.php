<?php

namespace TechnoSapiens;

class SectionTitleHeading {

    private const TAG_FIELD = 'title_heading_tag';
    private const SIZE_FIELD = 'title_heading_size';

    private const ALLOWED_TAGS = ['h2', 'h3', 'h4', 'h5', 'h6'];
    private const ALLOWED_SIZES = ['display', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    private const DEFAULT_TAG = 'h2';
    private const DEFAULT_SIZE = 'h2';

    public static function getTag(): string {
        $tag = get_field(self::TAG_FIELD) ?: self::DEFAULT_TAG;

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            return self::DEFAULT_TAG;
        }

        return $tag;
    }

    public static function getSize(): string {
        $size = get_field(self::SIZE_FIELD);

        if (!$size) {
            return self::DEFAULT_SIZE;
        }

        if (!in_array($size, self::ALLOWED_SIZES, true)) {
            return self::DEFAULT_SIZE;
        }

        return $size;
    }
}
