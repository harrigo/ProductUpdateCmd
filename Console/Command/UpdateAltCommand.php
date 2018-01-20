<?php
namespace Harrigo\ProductUpdateCmd\Console\Command;

use Magento\Catalog\Api\ProductManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAltCommand extends Command
{

    protected $productModel;
    protected $productRepository;
    protected $productManagement;
    protected $searchCriteriaBuilder;
    protected $registry;
    protected $state;
    
    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        ProductRepositoryInterface $productRepositoryInterface,
        Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        State $state
    ) {
        $this->productModel = $productModel;
        $this->productRepository = $productRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->registry = $registry;
        $this->state = $state;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('harrigo:updatealt')->setDescription('Updates Image Alt.');
        $this->addArgument('product_id', InputArgument::OPTIONAL, 'Start from Product ID');
        parent::configure();
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registry->register('isSecureArea', true, true);
        try {
            $this->state->getAreaCode();
        }
        catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
        if ($input->getArgument('product_id')) {
          $output->writeln('Updating Image Tags from ID: ' . $input->getArgument('product_id'));
        } else{
          $output->writeln('Updating Image Tags');
        } 
        if ($input->getArgument('product_id')) {
          $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $input->getArgument('product_id'), 'gteq')->create();
          $products = $this->productRepository->getList($searchCriteria);
        } else{
          $products = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        }
        if (count($products->getItems()) > 0)  {
            foreach ($products->getItems() as $product) {
                $output->writeln('Updating: ' . $product->getName());
                $title = $product->getName();
                $product = $this->productModel->load($product->getId());
                $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                if (count($existingMediaGalleryEntries) > 0) {
                    foreach ($existingMediaGalleryEntries as $key => $entry) {
                        $entry->setLabel($title);
                    }
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries)->setStoreId(0)->save();
                }
            }  
        } else {
            $output->writeln('No Products Found');
        }
    }
}

