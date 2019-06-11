<?php

namespace Microsimulation\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Helper\Callback;
use Microsimulation\Journal\Patterns\ViewModel\AboutProfile;
use Microsimulation\Journal\Patterns\ViewModel\AboutProfiles;
use Microsimulation\Journal\Patterns\ViewModel\ArticleSection;
use Microsimulation\Journal\Patterns\ViewModel\Button;
use Microsimulation\Journal\Patterns\ViewModel\ContentHeader;
use Microsimulation\Journal\Patterns\ViewModel\DefinitionList;
use Microsimulation\Journal\Patterns\ViewModel\FlexibleViewModel;
use Microsimulation\Journal\Patterns\ViewModel\FormLabel;
use Microsimulation\Journal\Patterns\ViewModel\IFrame;
use Microsimulation\Journal\Patterns\ViewModel\Link;
use Microsimulation\Journal\Patterns\ViewModel\ListHeading;
use Microsimulation\Journal\Patterns\ViewModel\Listing;
use Microsimulation\Journal\Patterns\ViewModel\Paragraph;
use Microsimulation\Journal\Patterns\ViewModel\SectionListing;
use Microsimulation\Journal\Patterns\ViewModel\SectionListingLink;
use Microsimulation\Journal\Patterns\ViewModel\SeeMoreLink;
use Microsimulation\Journal\Patterns\ViewModel\Select;
use Microsimulation\Journal\Patterns\ViewModel\SelectNav;
use Microsimulation\Journal\Patterns\ViewModel\SelectOption;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class AboutController extends Controller
{
    const FOUNDING_EDITOR_IN_CHIEF_ID = '6d42f4fe';

    public function aboutAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'About';

        $arguments['contentHeader'] = new ContentHeader('About eLife', null,
            'We want to improve all aspects of research communication in support of excellence in science');

        $arguments['body'] = [
            new Paragraph('eLife is a non-profit organisation inspired by research funders and led by scientists. Our mission is to help scientists accelerate discovery by operating a platform for research communication that encourages and recognises the most responsible behaviours in science.'),
            new Paragraph('eLife publishes work of the highest scientific standards and importance in all areas of the life and biomedical sciences. The research is selected and evaluated by working scientists and is made freely available to all readers without delay. eLife also invests in <a href="'.$this->get('router')->generate('about-innovation').'">innovation</a> through open-source tool development to accelerate research communication and discovery. Our work is guided by the <a href="'.$this->get('router')->generate('about-people').'">communities</a> we serve.'),
            new Paragraph('eLife was founded in 2011 by the Howard Hughes Medical Institute, the Max Planck Society and the Wellcome Trust​. These organisations continue to provide financial and strategic support, and were joined by the Knut and Alice Wallenberg Foundation for 2018. <a href="">Publication fees​</a>​ ​were introduced in 2017 ​to cover some of ​eLife\'s <a href="">core publishing costs</a>. <a href="">​Annual reports​</a>​ and financial statements are openly available.'),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function aimsScopeAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Aims and scope';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            ' ');

        $subjects = $this->get('elife.api_sdk.subjects')->reverse()->slice(0, 100);

        $arguments['body'] = [
            new Paragraph('The International Journal of Microsimulation (IJM) is the official online peer-reviewed journal of the International Microsimulation Association (<a href="https://microsimulation.org/storage/ijm_flyer.pdf">see flyer</a>).'),
            new Paragraph('The IJM covers research in all aspects of microsimulation modelling. It publishes high quality contributions making use of microsimulation models to address specific research questions in all scientific areas, as well as methodological and technical issues.'),
            new Paragraph('In particular, the IJM invites submission of five types of contributions: research articles, research notes, data watch, book reviews, and software reviews.'),
            new Paragraph('<strong>Research articles</strong> of interest to the IJM concern:'),
            Listing::unordered([
                'the description, validation, benchmarking and replication of microsimulation models;',
                'results coming from microsimulation models, in particular policy evaluation and counterfactual analysis;',
                'technical or methodological aspect of microsimulation modelling;',
                'reviews of models and results, as well as of technical or methodological issues.',
            ], 'bullet'),
            new Paragraph('<strong>Research notes</strong> concern:'),
            Listing::unordered([
                'specific technical aspects of microsimulation modelling,',
                'short case-studies illustrating the application of microsimulation models and their impacts on policy-making;',
                'examples of good practice in microsimulation modelling.',
            ], 'bullet'),
            new Paragraph('<strong>Data watch</strong> refers to short research notes that describe (newly) available datasets in detail.'),
            new Paragraph('<strong>Book reviews</strong> offer a discussion of recent books that might be of interest to the microsimulation community, or present a critical assessment in retrospect of the impact of "classic" contributions.'),
            new Paragraph('<strong>Software reviews</strong> are short contributions that describe advances in software development that are likely to be of interest to the journal readership, with a particular attention to open source software.'),
            new Paragraph('If in doubt concerning the suitability of a particular manuscript, or if interested in editing a Special thematic issue, please contact the editor for further advice.'),
            Listing::unordered([
                'The IJM is listed in EBSCOhost, EconLit, RePEc, Scopus.',
                'The ISSN of the journal is 1747-5864.',
                'The IDEAS/RePEc journal page can be accessed from <a href="https://ideas.repec.org/s/ijm/journl.html">here</a>.',
                'The IDEAS/RePEc impact factor of the journal is 2.094 (March 2018).',
                'The journal ranking page can be accessed from <a href="https://ideas.repec.org/top/top.series.simple.html#repec:ijm:journl">here</a>.',
            ], 'bullet'),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peerReviewAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Submission Policy';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            '');

        $arguments['body'] = [
            ArticleSection::basic('Ethics in publishing', 2,
                $this->render(new Paragraph('The IJM supports the ethical principles set out by the <a href="http://publicationethics.org/resources/guidelines">Committee on Publication Ethics (COPE).</a>')
                )
            ),
            ArticleSection::basic('Conflict of interest', 2,
                $this->render(new Paragraph('All Authors are requested to disclose any actual or potential conflict of interest. Further information and can be found in COPE author guidelines.')
                )
            ),
            ArticleSection::basic('Submission', 2,
                $this->render(new Paragraph('Submission of an article implies that the work described has not been published previously (except in the form of an abstract or as part of a published lecture or academic thesis or as an electronic preprint), that it is not under consideration for publication elsewhere, that its publication is approved by all authors and tacitly or explicitly by the responsible authorities where the work was carried out, and that, if accepted, it will not be published elsewhere in the same form, in English or in any other language, including electronically without the written consent of the copyright-holder.')
                )
            ),
            ArticleSection::basic('Copyright', 2,
                $this->render(new Paragraph('All IJM articles, unless otherwise stated, are published under the terms of the Creative Commons Attribution (CC BY) License which permits use, distribution and reproduction in any medium, provided the work is properly attributed back to the original author and publisher. Copyright on any research article in the International Journal ofMicrosimulation(IJM) is retained by the Authors. Authors grant IJM a license to publish the article and identify itself as the original publisher. Authors cannot revoke these freedoms as long as the Journal follows the license terms. Authors should not submit any paper unless they agree with this policy. The full text of the CC BY 4.0 license can be found here. Special exemptions and other licensing arrangement can be made on a case by case basis, by writing a motivated request to the Editor.')
                )
            ),
            ArticleSection::basic('Authors rights', 2,
                $this->render(new Paragraph('Contributors will retain the rights including but not limited to the following, as permitted by the CC BY license:')
                )
            ),
            Listing::unordered([
                'The rights to reproduce, distribute, publicly perform, and publicly display the Contribution in any medium for non-commercial purposes.',
                'The right to prepare derivative works from the Contribution, including reuse parts of the Contribution (e.g. figures and excerpts from an article) so long as the Authors receives credit as authors and the IJM is appropriately cited as the source of first publication.',
                'Patent and trademark rights and rights to any process or procedure described in the Contribution.',
                'The right to proper attribution and credit for the published work.',
            ], 'bullet'),
            ArticleSection::basic('Disclaimer', 2,
                $this->render(new Paragraph('The International Microsimulation Association (IMA) and the International Journal of Microsimulation (IJM) and make every effort to ensure the accuracy of all the information contained in our publications. It however, makes no representations or warranties whatsoever as to the accuracy, completeness, or suitability for any purpose of the published work. Any opinions and views expressed in this publication are the opinions and views of the Authors, and are not necessarily the view of the Editors or the Journal.')
                )
            ),

        ];
        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function opennessAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Note for authors';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            '');

        $arguments['body'] = [
            new Paragraph('Please see the <a href="about/aims-scope">Aims and scope</a> page for information about the types of contributions of interest to the journal. If in doubt concerning the suitability of a particular manuscript, please contact the editor for further advice.'),
            new Paragraph('<strong>Editorial policy</strong>'),
            new Paragraph('It is the policy of the journal to accept for consideration only original items written in English that have not previously been published and are not currently being considered for publication elsewhere. Prior release of material online or in a working paper series is not classed as publication unless subject to an independent peer review or refereeing process.'),
            new Paragraph('All submitted technical papers and case studies will be subject to peer review by two independent referees appointed by the editor. Other items will be accepted for publication subject to review by at least two members of the editorial board.'),
            new Paragraph('The International Journal of Microsimulation is made available to all without subscription. To support this publishing model, once an author has a paper accepted by the journal s/he is required to (i) ensure that their paper is formatted precisely as laid out in the journal\'s style guide; (ii) agree to peer review two papers by other authors submitted for consideration by the journal. The journal editors are responsible for ensuring that all submitted items are peer reviewed and published in a timely manner whilst maintaining the high standards expected of an academic journal.'),
            new Paragraph('Where appropriate, authors are invited to take advantage of the online nature of the journal by supplementing their written submissions with additional relevant material, such as listings of excerpts from executable code; downloadable working executables; extended results tables etc.'),
            new Paragraph('The International Journal of Microsimulation supports full transparency about data and code of published articles:'),
            new Paragraph('Authors are required to report, for any data they use, which is the source and whether the data is:'),
            Listing::ordered([
                'publicly available (specifying how the data can be accessed);',
                'available for scientific research only upon registration;',
                'proprietary (specifying the nature of the data and the user agreement which they benefited from).',
            ], 'number'),
            new Paragraph('If the paper is model-based, authors are also required to specify whether the code is:'),
            Listing::ordered([
                'open-source;',
                'proprietary, with executable available;',
                'proprietary, with executable also not available.',
            ], 'number'),
            new Paragraph('The journal encourages the use of open-source software and the publication of the source code.'),
            new Paragraph('For questions, please contact the Editor Matteo Richiardi at <a href="mailto:matteo.richiardi@essex.ac.uk">matteo.richiardi@essex.ac.uk</a>'),
            
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function innovationAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Notes for reviewers';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            '');

        $arguments['body'] = [
            new Paragraph('The primary purpose of the review process is to ensure that papers accepted for publication by the journal meet the highest academic standards of clarity, rigour and replicability. A secondary purpose of the review process is to provide constructive feedback for authors whose papers fall short of this mark.'),
            new Paragraph('The general expectation is that all reviewer feedback will be passed on, in an anonymised form, to the submitting author(s). Reviewers are therefore asked to ensure that their comments are suitably constructive, and to highlight any comments that they would prefer remained confidential to the editor.'),
            new Paragraph('In providing feedback on a paper, reviewers are asked make a clear overall recommendation (publish; publish subject to minor revision; publish subject to major revision; revise and resubmit; reject). This recommendation should be followed by a justification for the recommendation made, including at least brief reference to each of:'),
            Listing::unordered([
                'Originality',
                'Validity of methods, results and interpretations',
                'Relevance to journal readership',
                'Clarity and structure of narrative',
                'Quality and appropriateness of any tables or <figures></figures>',
            ], 'bullet'),
            new Paragraph('If revision prior to publication or resubmission is recommended, reviewers are asked to provide a list of points that the submitting author(s) should be asked to address.'),
            new Paragraph('In order to allow for timely publication, reviewers are asked to provide comments on submitted items within the agreed review deadline (normally four weeks after receipt of item).'),

        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function earlyCareerAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Early-careers';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'The community behind eLife wants to help address some of the pressures on early-career scientists');

        $arguments['body'] = [
            new Paragraph('The community behind eLife – including the research funders who support the journal, the editors and referees who run the peer-review process, and our Early-Career Advisory Group – are keenly aware of the pressures faced by early-stage investigators. That’s one reason we’re working to create a more positive publishing experience that will, among other things, help early-career researchers receive the recognition they deserve.'),
            new Paragraph('eLife supports and showcases early-career scientists and their work in a number of ways:'),
            new DefinitionList([
                'Early-Career Advisory Group' => $this->render(
                    new Paragraph('eLife has invited a group of talented graduate students, postdocs and junior group leaders from across the world to our <a href="'.$this->get('router')->generate('about-people', ['type' => 'early-career']).'">Early-Career Advisory Group</a>. The ECAG acts as a voice for early-career researchers (ECRs) within eLife, representing their needs and aspirations and helping to develop new initiatives and shape current practices to change academic publishing for the better.'),
                    new Paragraph('The role of the ECAG includes:'),
                    Listing::unordered([
                        'Offering ideas and advice on new and ongoing efforts with the potential to help early-career scientists',
                        'Providing direct support for ongoing programs, such as monthly webinars',
                        'Leading efforts to reach out to early-stage colleagues, to gather their feedback and/or connect them to the network',
                        'Participating in online or in-person events about issues of concern to early-stage researchers',
                        'Attending quarterly phone calls and an annual in-person meeting',
                    ], 'bullet'),
                    new Paragraph('For more information, take a look at this <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '140901c3']).'">video message from the ECAG</a>.')
                ),
                'Community Ambassadors' => 'We convene and facilitate a worldwide community of like-minded researchers, led by the ECAG. The eLife Community Ambassadors champion responsible behaviours in science among colleagues and create and deliver solutions that accelerate positive changes in scholarly culture.',
                'Involvement in peer review' => 'eLife encourages reviewers to involve junior colleagues as co-reviewers; we involve outstanding early-stage researchers as reviewers <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '31a5173b']).'">in their own right</a>; and we enable all reviewers to receive credit for their contributions through services such as Publons and ORCID',
                'Travel grants' => 'eLife offers funding to help early-career scientists get exposure and recognition for their work among leading scientists in their fields. New travel grant programmes are announced at the start of each year. Sign up to our <a href="https://crm.elifesciences.org/crm/community-news">early-career newsletter</a> for updates and information on how to apply.',
                'Webinars' => 'A platform for the early-career community to share opportunities and explore issues around building a successful research career, on the last Wednesday of the month. Previous webinars can be found on our <a href="'.$this->get('router')->generate('collection', ['id' => '842f35d5']).'">collection page</a>.',
                'Magazine features' => 'Early-career researchers and issues of concern to them are regularly featured in interviews, podcasts and articles in the <a href="'.$this->get('router')->generate('magazine').'">Magazine section</a> of eLife',
            ]),
            new Paragraph('For the latest in our work to support early-career scientists, explore our <a href="'.$this->get('router')->generate('community').'">Community page</a> and sign up for eLife <a href="https://crm.elifesciences.org/crm/community-news">News for Early-Career Researchers</a>. You can also find us on Twitter: <a href="https://twitter.com/eLifeCommunity">@eLifeCommunity</a>'),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peopleAction(Request $request, string $type) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Editorial Board';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            '');
        $arguments['body'] = [
            new Paragraph('The editors, associate editors and editorial board of the International Journal of Microsimulation are appointed through the auspices of the International Microsimulation Association. All are appointed to serve for a two-year period, during which they commit themselves to (i) seeking out and encouraging submission of work likely to be of interest to the journal readership; (ii) undertaking reviews of submitted manuscripts; (iii) providing guidance to the editor on future directions for the journal. In addition, Associate Editors take responsibility for facilitating the review and editing of submitted manuscripts falling within their area of subject specialism.  Nominations for all of these posts are sought in the run-up to the biennial IMA General Conference via the IMA-NEWS email discussion list.'),
            new Paragraph('<strong>Chief Editor</strong> - Prof Matteo Richiardi (University of Essex, UK)'),
            new Paragraph('<strong>Assistant Editors</strong>'),
            Listing::unordered([
                'Dr Ernesto Carrella (University of Oxford, UK)',
                'Dr Melanie Tomintz (University of Canterbury, New Zealand)',
            ], 'bullet'),
            new Paragraph('<strong>Book Review Editor</strong> - Dr Eveline van Leeuwen (VU University Amsterdam, the Netherlands)'),
            new Paragraph('<strong>Associate Editors</strong>'),
            Listing::unordered([
                'Prof John Cockburn (Université Laval, Canada)',
                'Prof Francesco Figari (Università dell\'Insubria, Italy)',
                'Dr Sophie Pennec (Institut National d\'Etudes Démographiques, France)',
                'Dr Azizur Rahman (Charles Sturt University, Australia)',
                'Prof Andrea Roventini (Scuola Superiore S. Anna, Italy)',
                'Prof Deborah Schofield (University of Sydney, Australia)',
                'Prof Venky Shankar (Pensylvania State University, USA)',
                'Dr Sven Stöwhase (FIT, Germany)',
                'Dr Gerlinde Verbist (Antwerp University, Belgium)',
                'Dr Sander van der Hoog (University of Bielefeld, Germany)',
                'Jürgen Wiemers (IAB, Germany)',
            ], 'bullet'),
            new Paragraph('<strong>Scientific Committee</strong>'),
            Listing::unordered([
                'Prof Rolf Aaberge (Statistics Norway)',
                'Prof Jakub Bijak (Univesity of Southampton, UK)',
                'Prof Francesco Billari (University of Oxford, UK)',
                'Prof Ugo Colombino (University of Torino, Italy)',
                'Prof John Creedy (University of Melbourne, Australia)',
                'Prof André Decoster (University of Leuven, Belgium)',
                'Dr Gijs Dekkers (Federal Panning Bureau, Belgium)',
                'Prof Lennart Flood (University of Gothenburg, Sweden)',
                'Prof Cathal O\'Donoghue (Teagasc, Ireland)',
                'Prof Andreas Peichl (University of Mannheim, Germany)',
                'Prof Nicole Saam (Erlangen University, Germany)',
                'Prof Holly Sutherland (University of Essex, UK)',
                'Prof Leigh Tesfatsion (Iowa State University, USA)',
                'Prof Michael Wolfson (University of Ottawa, Canada)',
            ], 'bullet'),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    private function createAboutProfiles(Sequence $people, string $heading, bool $compact = false)
    {
        if ($people->isEmpty()) {
            return null;
        }

        return new AboutProfiles($people->map($this->willConvertTo(AboutProfile::class, compact('compact')))->toArray(), new ListHeading($heading), $compact);
    }

    private function aboutPageArguments(Request $request) : array
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['menuLink'] = new SectionListingLink('All sections', 'sections');

        $menuItems = [
            'Aims and scope' => $this->get('router')->generate('about-aims-scope'),
            'Editorial board' => $this->get('router')->generate('about-people'),
            'Submission policy' => $this->get('router')->generate('about-peer-review'),
            'Notes for authors' => $this->get('router')->generate('about-openness'),
            'Notes for reviewers' => $this->get('router')->generate('about-innovation'),
        ];

        $currentPath = $this->get('router')->generate($request->attributes->get('_route'), $request->attributes->get('_route_params'));

        $menuItems = array_map(function (string $text, string $path) use ($currentPath) {
            return new Link($text, $path, $path === $currentPath);
        }, array_keys($menuItems), array_values($menuItems));

        $arguments['menu'] = new SectionListing('sections', $menuItems, new ListHeading('About sections'), true);

        return $arguments;
    }
}
