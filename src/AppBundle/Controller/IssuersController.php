<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Traits;
use AppBundle\Presenter\Organism\EntityContext\EntityContextPresenter;
use AppBundle\Presenter\Organism\EntityNav\EntityNavPresenter;
use AppBundle\Presenter\Organism\Issuer\IssuerPresenter;
use SecuritiesService\Domain\Exception\EntityNotFoundException;
use SecuritiesService\Domain\Exception\ValidationException;
use SecuritiesService\Domain\ValueObject\UUID;
use SecuritiesService\Service\Filter\SecuritiesFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IssuersController extends Controller
{
    use Traits\MaturityProfileTrait;
    use Traits\SecuritiesTrait;
    use Traits\IssuanceTrait;
    use Traits\OverviewTrait;
    use Traits\FinderTrait;

    public function initialize(Request $request)
    {
        parent::initialize($request);
        $this->toView('currentSection', 'issuers');
    }

    public function listAction()
    {
        $perPage = 1500;
        $currentPage = $this->getCurrentPage();

        $total = $this->get('app.services.issuers')
            ->countAll();
        $issuers = [];
        if ($total) {
            $issuers = $this->get('app.services.issuers')
                ->findAll($perPage, $currentPage);
        }

        $issuerPresenters = [];
        if (!empty($issuers)) {
            foreach ($issuers as $issuer) {
                $issuerPresenters[] = new IssuerPresenter($issuer);
            }
        }

        $letterGroups = [];
        foreach ($issuerPresenters as $issuer) {
            if (!isset($letterGroups[$issuer->getLetter()])) {
                $letterGroups[$issuer->getLetter()] = [];
            }
            $letterGroups[$issuer->getLetter()][] = $issuer;
        }

        // move numbers to the end
        $letterGroups['0-9'] = array_shift($letterGroups);

        $allLetters = array_merge(range('A', 'Z'), ['0-9']);
        $letters = [];
        foreach ($allLetters as $letter) {
            $letters[] = (object) [
                'width' => ($letter == '0-9') ? '1/2' : '1/4',
                'text' => $letter,
                'active' => isset($letterGroups[$letter]),
            ];
        }

        $this->setTitle('Issuers');
        $this->toView('letters', $letters);
        $this->toView('groups', $letterGroups);
        $this->toView('total', $total);

        $this->setPagination(
            $total,
            $currentPage,
            $perPage
        );

        return $this->renderTemplate('issuers:list');
    }

    public function showAction(Request $request)
    {
        $issuer = $this->getIssuer($request);
        return $this->renderOverview($request, $issuer);
    }

    public function securitiesAction(Request $request)
    {
        $issuer = $this->getIssuer($request);
        return $this->renderSecurities($request, $issuer);
    }

    public function maturityProfileAction(Request $request)
    {
        $issuer = $this->getIssuer($request);
        return $this->renderMaturityProfile($request, $issuer);
    }

    public function issuanceAction(Request $request)
    {
        $issuer = $this->getIssuer($request);
        return $this->renderIssuance($request, $issuer);
    }

    private function getIssuer(Request $request)
    {
        $id = $request->get('issuer_id');

        try {
            $issuer = $this->get('app.services.issuers')
                ->findByUUID(UUID::createFromString($id));
        } catch (ValidationException $e) {
            throw new HttpException(404, $e->getMessage());
        } catch (EntityNotFoundException $e) {
            throw new HttpException(404, $e->getMessage());
        }

        $sector = null;
        $industry = null;

        $group = $issuer->getParentGroup();
        if ($group) {
            $sector = $group->getSector();
        }
        if ($sector) {
            $industry = $sector->getIndustry();
        }

        $this->toView('issuer', $issuer, true);
        $this->toView('entityContextPresenter', new EntityContextPresenter($issuer));

        // I'm looking at a group, so I need to pass in that issuer,
        // and it's parent group, sector + industry
        $this->setFinder($request->get('_route'), $industry, $sector, $group, $issuer);
        return $issuer;
    }
}
