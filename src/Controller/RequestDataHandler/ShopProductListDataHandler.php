<?php

/*
 * This file has been created by developers from BitBag. 
 * Feel free to contact us once you face any issues or want to start
 * another great project. 
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl. 
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ShopProductListDataHandler implements DataHandlerInterface
{
    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var LocaleContextInterface
     */
    private $localeContext;

    /**
     * @var string
     */
    private $nameProperty;

    /**
     * @var PaginationDataHandler
     */
    private $paginationDataHandler;

    /**
     * @var string
     */
    private $taxonsProperty;

    /**
     * @var string
     */
    private $optionPropertyPrefix;

    /**
     * @var string
     */
    private $attributePropertyPrefix;

    /**
     * @param TaxonRepositoryInterface $taxonRepository
     * @param LocaleContextInterface $localeContext
     * @param PaginationDataHandler $paginationDataHandler
     * @param string $nameProperty
     * @param string $taxonsProperty
     * @param string $optionPropertyPrefix
     * @param string $attributePropertyPrefix
     */
    public function __construct(
        TaxonRepositoryInterface $taxonRepository,
        LocaleContextInterface $localeContext,
        PaginationDataHandler $paginationDataHandler,
        string $nameProperty,
        string $taxonsProperty,
        string $optionPropertyPrefix,
        string $attributePropertyPrefix
    )
    {
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
        $this->paginationDataHandler = $paginationDataHandler;
        $this->nameProperty = $nameProperty;
        $this->taxonsProperty = $taxonsProperty;
        $this->optionPropertyPrefix = $optionPropertyPrefix;
        $this->attributePropertyPrefix = $attributePropertyPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveData(Request $request): array
    {
        $slug = $request->get('taxonSlug');
        $taxon = $this->taxonRepository->findOneBySlug($slug, $this->localeContext->getLocaleCode());

        if (null === $taxon) {
            throw new NotFoundHttpException();
        }

        $data = $this->paginationDataHandler->retrieveData($request);
        $data[$this->nameProperty] = $request->query->get($this->nameProperty);
        $data[$this->taxonsProperty] = strtolower($taxon->getCode());

        $this->handlePrefixedProperty($request, $this->optionPropertyPrefix, $data);
        $this->handlePrefixedProperty($request, $this->attributePropertyPrefix, $data);

        return $data;
    }

    /**
     * @param Request $request
     * @param string $propertyPrefix
     * @param array $data
     */
    private function handlePrefixedProperty(Request $request, string $propertyPrefix, array &$data): void
    {
        foreach ($request->query->all() as $key => $value) {
            if (is_array($value) && 0 === strpos($key, $propertyPrefix)) {
                $data[$key] = array_map('strtolower', $value);
            }
        }
    }
}
