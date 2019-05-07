<?php

namespace Microsimulation\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\SoftwareReference;
use Microsimulation\Journal\ViewModel\Converter\ViewModelConverter;
use Microsimulation\Journal\Patterns\ViewModel;

final class SoftwareReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param SoftwareReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getTitle();
        if ($object->getVersion()) {
            $title .= ', version '.$object->getVersion();
        }

        // hack for missing date
        if ($object->getDate()->getYear() > 1000) {
            $authorsSuffix = [$object->getDate()->format().$object->getDiscriminator()];
        } else {
            $authorsSuffix = [];
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), $authorsSuffix)];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($title, $object->getUri()), [$object->getPublisher()->toString()], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof SoftwareReference;
    }
}
