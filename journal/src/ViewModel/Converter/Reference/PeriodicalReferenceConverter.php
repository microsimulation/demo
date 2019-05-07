<?php

namespace Microsimulation\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use Microsimulation\Journal\ViewModel\Converter\ViewModelConverter;
use Microsimulation\Journal\Patterns\ViewModel;

final class PeriodicalReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PeriodicalReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $periodical = '<i>'.$object->getPeriodical().'</i>';
        if ($object->getVolume()) {
            $periodical .= ' <b>'.$object->getVolume().'</b>:';
            if ($object->getPages() instanceof ReferencePageRange) {
                $periodical .= $object->getPages()->getRange();
            } else {
                $periodical .= $object->getPages()->toString();
            }
        } elseif ($object->getPages()) {
            $periodical .= ' '.$object->getPages()->toString();
        }

        // hack for missing date
        if ($object->getDate()->getYear() > 1000) {
            $authorsSuffix = [$object->getDate()->format().$object->getDiscriminator()];
        } else {
            $authorsSuffix = [];
        }

        $referenceAuthors = $this->pruneAuthors($object->getAuthors());

        $authors = [$this->createAuthors($referenceAuthors, $object->authorsEtAl(), $authorsSuffix)];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getArticleTitle(), $object->getUri()), [$periodical], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PeriodicalReference;
    }
}
