<?php

namespace Microsimulation\Journal\ViewModel\Converter;

use Microsimulation\Journal\Helper\Paginator;
use Microsimulation\Journal\ViewModel\EmptyListing;
use Microsimulation\Journal\Patterns\ViewModel;
use Microsimulation\Journal\Patterns\ViewModel\ListHeading;

final class PaginatorListingTeasersConverter implements ViewModelConverter
{
    /**
     * @param Paginator $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $type = $context['type'] ?? null;

        if (isset($context['heading']) && '' === $context['heading']) {
            $heading = null;
        } else {
            $heading = new ListHeading($context['heading'] ?? trim('Latest '.$type));
        }
        $prevText = trim('Newer '.$type);
        $nextText = trim('Older '.$type);
        $emptyText = $context['emptyText'] ?? (trim('No '.($type ?? 'items').' available.'));

        if (0 === count($object->getItems())) {
            return new EmptyListing($heading, $emptyText);
        } elseif ($object->getCurrentPage() > 1) {
            return $viewModel::withPagination(
                $object->getItems(),
                ViewModel\Pager::subsequentPage(
                    new ViewModel\Link($prevText, $object->getPreviousPagePath()),
                    $object->getNextPage()
                        ? new ViewModel\Link($nextText, $object->getNextPagePath())
                        : null
                )
            );
        } elseif ($object->getNextPage()) {
            return $viewModel::withPagination(
                $object->getItems(),
                $object->getNextPage()
                    ? ViewModel\Pager::firstPage(new ViewModel\Link('Load more', $object->getNextPagePath()), 'listing')
                    : null,
                $heading,
                'listing'
            );
        }

        return $viewModel::basic($object->getItems(), $heading, 'listing');
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Paginator && in_array($viewModel, [ViewModel\ListingTeasers::class, ViewModel\ListingAnnotationTeasers::class]);
    }
}
