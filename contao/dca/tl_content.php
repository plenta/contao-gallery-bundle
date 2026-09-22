<?php

declare(strict_types=1);

/**
 * Plenta Gallery Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2026, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['plenta_gallery'] = '
    {type_legend},type,galleryTitle,galleryAlias,headline,galleryDate;
    {text_legend},galleryText,galleryLinkText;
    {gallery_legend},galleryFolder,gallerySortBy,size,fullsize,perRow,perPage,serverPagination,galleryCover,galleryCoverSize;
    {template_legend:hide},customTpl;
    {protected_legend:hide},protected;
    {expert_legend:hide},cssID;
    {invisible_legend:hide},invisible,start,stop
';

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryTitle'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryAlias'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) BINARY NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryDate'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
    'sql' => 'int(10) unsigned NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryText'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'basicEntities' => true, 'helpwizard' => true, 'tl_class' => 'clr'],
    'explanation' => 'insertTags',
    'sql' => 'mediumtext NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryLinkText'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryFolder'] = [
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['files' => false, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['gallerySortBy'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content'],
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => "varchar(32) COLLATE ascii_bin NOT NULL default 'name_asc'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryCover'] = [
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => [
        'filesOnly' => true,
        'fieldType' => 'radio',
        'extensions' => '%contao.image.valid_extensions%',
        'tl_class' => 'clr',
    ],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['galleryCoverSize'] = [
    'exclude' => true,
    'inputType' => 'imageSize',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'options_callback' => ['contao.listener.image_size_options', '__invoke'],
    'eval' => [
        'rgxp' => 'natural',
        'includeBlankOption' => true,
        'nospace' => true,
        'helpwizard' => true,
        'tl_class' => 'w50 clr',
    ],
    'sql' => [
        'type' => 'string',
        'length' => 255,
        'default' => '',
        'platformOptions' => ['collation'=>'ascii_bin']
    ],
];
