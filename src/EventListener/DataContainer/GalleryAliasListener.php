<?php

declare(strict_types=1);

/**
 * Plenta Gallery Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2026, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\GalleryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: 'tl_content', target: 'fields.galleryAlias.save')]
class GalleryAliasListener
{
    public function __construct(
        private readonly Slug $slug,
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(mixed $value, DataContainer $dc): string
    {
        $value = (string) $value;
        $id = (int) $dc->id;

        $aliasExists = fn (string $alias): bool => false !== $this->connection->fetchOne(
            'SELECT id FROM tl_content WHERE type = ? AND galleryAlias = ? AND id != ?',
            ['plenta_gallery', $alias, $id],
        );

        if ('' === $value) {
            return $this->slug->generate($this->getTitle($dc), [], $aliasExists);
        }

        if ($aliasExists($value)) {
            throw new \RuntimeException($this->translator->trans('ERR.aliasExists', [$value], 'contao_default'));
        }

        return $value;
    }

    private function getTitle(DataContainer $dc): string
    {
        $submitted = $this->requestStack->getCurrentRequest()?->request->get('galleryTitle');

        if (\is_string($submitted) && '' !== trim($submitted)) {
            return $submitted;
        }

        return (string) ($dc->getCurrentRecord()['galleryTitle'] ?? '');
    }
}
