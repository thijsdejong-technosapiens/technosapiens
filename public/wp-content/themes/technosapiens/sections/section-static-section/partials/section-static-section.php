<?php

use TechnoSapiens\SectionStaticSection;

$staticBlockId = SectionStaticSection::getStaticBlockId();
$staticBlockHtml = SectionStaticSection::renderSelectedStaticBlock($staticBlockId);

if (!$staticBlockHtml) {
    return;
}

echo $staticBlockHtml;
