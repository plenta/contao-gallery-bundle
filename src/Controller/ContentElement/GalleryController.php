<?php

declare(strict_types=1);

/**
 * Plenta Gallery Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2026, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\GalleryBundle\Controller\ContentElement;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\PageOutOfRangeException;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemItemIterator;
use Contao\CoreBundle\Filesystem\FilesystemUtil;
use Contao\CoreBundle\Filesystem\SortMode;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\CoreBundle\Pagination\PaginationFactoryInterface;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Date;
use Contao\Input;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsContentElement('plenta_gallery', category: 'media')]
class GalleryController extends AbstractContentElementController
{
    /**
     * @param array<string> $validExtensions
     */
    public function __construct(
        #[Autowire(service: 'contao.filesystem.virtual.files')]
        private readonly VirtualFilesystemInterface $filesStorage,
        #[Autowire(service: 'contao.image.studio')]
        private readonly Studio $studio,
        #[Autowire(param: 'contao.image.valid_extensions')]
        private readonly array $validExtensions,
        private readonly Connection $connection,
        #[Autowire(service: 'contao.pagination.factory')]
        private readonly PaginationFactoryInterface $paginationFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $alias = (string) $model->galleryAlias;
        $page = $this->getPageModel();
        $isGallery = false;

        if (!$this->isBackendScope($request)) {
            $autoItem = (string) $this->getContaoAdapter(Input::class)->get('auto_item');
            $isGallery = '' !== $alias && $autoItem === $alias;

            if (!$isGallery && '' !== $autoItem && $this->isGalleryAlias($autoItem)) {
                return new Response();
            }
        }

        $template->set('mode', $isGallery ? 'reader' : 'teaser');
        $template->set('gallery_title', (string) $model->galleryTitle);
        $template->set('gallery_date', $this->getDate($model, $page));
        $template->set('text', (string) $model->galleryText);
        $template->set('link_text', (string) $model->galleryLinkText);
        $template->set('items_per_row', $model->perRow ?: null);
        $template->set('href', null);
        $template->set('back_href', null);
        $template->set('cover', null);
        $template->set('image_count', null);
        $template->set('images', []);

        if ($isGallery) {
            $this->addImages($template, $model);
            $template->set('back_href', $page ? $this->generateContentUrl($page) : null);

            return $template->getResponse();
        }

        $template->set('image_count', $this->getItems($model)->count());

        if ($model->galleryCover) {
            $template->set('cover', $this->studio
                ->createFigureBuilder()
                ->from($model->galleryCover)
                ->setSize($model->galleryCoverSize)
                ->buildIfResourceExists()
            );
        }

        if ($page && '' !== $alias) {
            $template->set('href', $this->generateContentUrl($page, ['parameters' => '/'.$alias]));
        }

        return $template->getResponse();
    }

    private function isGalleryAlias(string $alias): bool
    {
        return false !== $this->connection->fetchOne(
            'SELECT id FROM tl_content WHERE type = ? AND galleryAlias = ? AND invisible = 0 LIMIT 1',
            ['plenta_gallery', $alias],
        );
    }

    /**
     * @return array{timestamp: int, datetime: string, formatted: string}|null
     */
    private function getDate(ContentModel $model, PageModel|null $page): array|null
    {
        if (!$model->galleryDate) {
            return null;
        }

        $timestamp = (int) $model->galleryDate;
        $format = $page?->dateFormat ?: $this->getContaoAdapter(Config::class)->get('dateFormat');

        return [
            'timestamp' => $timestamp,
            'datetime' => date('Y-m-d', $timestamp),
            'formatted' => $this->getContaoAdapter(Date::class)->parse($format, $timestamp),
        ];
    }

    private function getItems(ContentModel $model): FilesystemItemIterator
    {
        return FilesystemUtil::listContentsFromSerialized($this->filesStorage, $model->galleryFolder)
            ->filter(fn (FilesystemItem $item): bool => \in_array($item->getExtension(true), $this->validExtensions, true))
        ;
    }

    private function addImages(FragmentTemplate $template, ContentModel $model): void
    {
        $items = $this->getItems($model);

        if ($sortMode = SortMode::tryFrom((string) $model->gallerySortBy)) {
            $items = $items->sort($sortMode);
        }

        $randomize = 'random' === $model->gallerySortBy;

        $template->set('sort_mode', $sortMode);
        $template->set('randomize_order', $randomize);

        $figureBuilder = $this->studio
            ->createFigureBuilder()
            ->setSize($model->size)
            ->setLightboxGroupIdentifier('lb'.$model->id)
            ->enableLightbox((bool) $model->fullsize)
        ;

        $images = array_values(array_filter(array_map(
            fn (FilesystemItem $item): Figure|null => $figureBuilder
                ->fromStorage($this->filesStorage, $item->getPath())
                ->buildIfResourceExists(),
            iterator_to_array($items),
        )));

        if ($model->perPage > 0 && $model->serverPagination && !$randomize) {
            try {
                $pagination = $this->paginationFactory->create(new PaginationConfig('page_g'.$model->id, \count($images), $model->perPage));
            } catch (PageOutOfRangeException $e) {
                throw new PageNotFoundException('Page not found', previous: $e);
            }

            $template->set('pagination', $pagination);
            $images = $pagination->getItemsForPage($images);
        }

        $template->set('images', $images);
        $template->set('items_per_page', $model->perPage ?: null);
    }
}
