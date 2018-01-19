<?php
namespace Harrigo\ProductUpdateCmd\Console\Command;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAltCommand extends Command
{
    protected $productModel;
    protected $productRepositoryInterface;
    protected $searchCriteriaBuilder;
    protected $registry;
    protected $state;
    
    public function __construct(
        Product $productModel,
        ProductRepositoryInterface $productRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        State $state
    ) {
        $this->productModel = $productModel;
        $this->productRepository = $productRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->state = $state;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('harrigo:updatealt')->setDescription('Updates Image Alt.');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        try {
            $this->state->getAreaCode();
        }
        catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
        
        $output->writeln('Updating Image Tags' . PHP_EOL);

        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        
        if (count($products->getItems()) > 0)  {
            foreach ($products->getItems() as $product) {
                $output->writeln('Updating: ' . $product->getName() . PHP_EOL);
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
            $output->writeln('No Products Found' . PHP_EOL);
        }
    }
}

