<?php
/**
 * LICENSE
 *
 * This source file is subject to the BSD 3-Clause license.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to saulotoledo@gmail.com so we can send you a copy immediately.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Controllers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Controller de pedidos.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Controllers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_ProductsController extends Zend_Controller_Action
{
    /**
     * Inicialização do controller.
     *
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->loadShoppingCart();
        $this->loadCategories();
    }

    /**
     * Carrega as categorias para a view.
     */
    private function loadCategories()
    {
        //TODO: Adicionar suporte à tradução também às categorias.
        $this->view->categories = Order_Model_ProductCategory::fetchTree();
    }

    /**
     * Prepara um array com informações de de produtos para a view.
     *
     * @param  array $productsList Uma lista de objetos Order_Model_Product
     *         com as informações dos produtos a utilizar.
     * @return array Um array com informações de de produtos para a view.
     */
    private function prepareShoppingCartProducts($productsList)
    {
        $viewProducts = array();

        foreach ($productsList as $productId => $productQuantity) {
            $product = new Order_Model_Product();
            $product->find($productId);

            $viewProducts[] = (object) array(
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'image' => $this->getResizedImagePath(
                    $product->getImage(),
                    45,
                    45
                ),
                'price' => $product->getPrice(),
                'categories' => null,
                'features' => null,
                'quantity' => $productQuantity
            );
        }

        return $viewProducts;
    }

    /**
     * Carraga o carrinho de compras.
     */
    private function loadShoppingCart()
    {
        $this->view->shoppingCart = null;
        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');

        if ($shoppingCart != null) {
            $productsList = $shoppingCart->getProducts();
            $this->view->shoppingCart = $this->prepareShoppingCartProducts($productsList);
        }
    }

    /**
     * Cria um objeto Zend_Paginator de acordo com
     * informações recebidas nos parâmetros.
     *
     * @param  int $numItens O número de itens a paginar.
     * @param  int $itemsPerPage A quantidade de itens por página.
     * @param  int $page O número da página atual.
     * @return Zend_Paginator O objeto Zend_Paginator responsável pela paginação
     *         dos dados.
     */
    protected function __setupPaginator($numItens, $itemsPerPpage, $page)
    {
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($numItens));

        $paginator
            ->setPageRange(Zend_Registry::get('systemConfig')->pagination->defaultpagerange)
            ->setItemCountPerPage($itemsPerPpage)
            ->setCurrentPageNumber($page)
        ;

        return $paginator;
    }

    /**
     * Filtra produtos por categoria.
     */
    public function categoryAction()
    {
        $filters = $this->_request->getParam('filters');
        if ($filters == null) {
            $filters = array();
        }
        $filters['category_id'] = $this->_request->getParam('id');
        $this->_request->setParam('filters', $filters);

        $category = new Order_Model_ProductCategory();
        $category->find($this->_request->getParam('id'));
        if ($category->getId() != null) {
            $tree = $this->generateCategoriesTreeFor(array($category));
            $this->view->category = $tree[0];
        }

        $this->_forward('show', 'products', 'order');
    }

    /**
     * Ação padrão do controller. Limpa os filtros e redireciona
     * para a visão de produtos.
     */
    public function indexAction()
    {
        $auxFilters = Auth_Model_SystemAuth::getInstance()->setAuthVariable('filters', null);
        $this->_forward('show', 'products', 'order');
    }

    /**
     * Descobre os ancestrais de um conjunto de categorias informado
     * e monta uma árvore em formato de array desde o maior ancestral
     * até cada categoria informada. O resultado é um conjunto formado
     * por todas estas árvores de categorias, uma para cada categoria
     * informada.
     *
     * @param  array $productCategories Uma lista de objetos do tipo
     *         Order_Model_ProductCategory a utilizar.
     * @return array A árvore de categorias de acordo com o especificado.
     */
    private function generateCategoriesTreeFor($productCategories)
    {
        $categoriesTree = array();
        foreach ($productCategories as $categoryObject) {
            $category = array(
                'id' => $categoryObject->getId(),
                'name' => $categoryObject->getName()
            );

            while ($categoryObject->getParent() != null) {
                $categoryObject = $categoryObject->getParent();
                $category = array(
                    'id' => $categoryObject->getId(),
                    'name' => $categoryObject->getName(),
                    'child' => $category
                );
            }

            $categoriesTree[] = $category;
        }

        return $categoriesTree;
    }


    /**
     * Apresentação da lista de produtos segundo os filtros
     * informados na view.
     */
    //TODO: Método muito grande, refazer.
    //TODO: Revisar o uso de $this->escape() em todas as views
    public function showAction()
    {
        $page = $this->_request->getParam('page', 1);
        $sessionKey = $this->_request->getModuleName() .'_'. $this->_request->getControllerName() .'_'. $this->_request->getActionName();

        $auxLimit = Auth_Model_SystemAuth::getInstance()->getAuthVariable('limit');
        $auxFilters = Auth_Model_SystemAuth::getInstance()->getAuthVariable('filters');

        // Converter uma string para inteiro com o cast abaixo retornará 0 (zero) se o valor não for um inteiro.
        $itemsPerPage = (int) $this->_request->getParam('limit', $auxLimit[$sessionKey]);

        $auxOrder = Auth_Model_SystemAuth::getInstance()->getAuthVariable('order');
        $viewType = Auth_Model_SystemAuth::getInstance()->getAuthVariable('viewtype');
        $viewType = $viewType[$sessionKey];

        $orderField = null;
        $orderDir = null;
        $aux = explode(' ', $auxOrder[$sessionKey]);
        $orderField = $aux[0];
        if (count($aux) > 1) {
            $orderDir = $aux[1];
        }
        unset($aux);

        $filters = null;
        if (isset($auxFilters[$sessionKey])) {
            $filters = $this->_request->getParam('filters', $auxFilters[$sessionKey]);
        }

        $zendFilter = new Zend_Filter_StripTags();
        if (!is_null($filters)) {
            foreach ($filters as $filterKey => $filterValue) {
                $filters[$filterKey] = $zendFilter->filter($filterValue);
            }
        }

        $registers = new Order_Model_Product();
        $numItens = $registers->countAll($filters);

        if ($itemsPerPage <= 0) {
            $itemsPerPage = $numItens;
        }

        // Ajuste quando o número de páginas muda para menos após uso de um filtro pelo usuário:
        if (ceil($numItens / $itemsPerPage) < $page) {
            $page = ceil($numItens / $itemsPerPage);
        }

        // Setup da paginação:
        $paginator = $this->__setupPaginator(
            $numItens,
            $itemsPerPage,
            $page
        );

        $registers = $registers->fetchAll($auxOrder[$sessionKey], $page, $itemsPerPage, $filters);

        $rowNumber = 1 + (($page - 1) * $itemsPerPage);
        $registersArray = array();

        foreach ($registers as $register) {
            // TODO: Remover números mágicos de altura e largura a seguir.
            // TODO: Não repassar para a view os objetos Order_Model_DbTable_ProductFeatures e Order_Model_DbTable_ProductCategories.
            $registersArray[] = (object) array(
                'number'      => $rowNumber,
                'id'          => $register->getId(),
                'name'        => $register->getName(),
                'description' => $register->getDescription(),
                'image'       => $this->getResizedImagePath(
                    $register->getImage(),
                    ($viewType == 'icon') ? 300 : 90,
                    ($viewType == 'icon') ? 200 : 90
                ),
                'price'       => $register->getPrice(),
                'categories'  => $this->generateCategoriesTreeFor($register->getCategories()),
                'features'    => $register->getFeatures()
            );

            $rowNumber++;
        }

        if (isset($filters['expr1'])) {
            if (count($registersArray) > 0) {
                $this->view->message = sprintf(
                    Zend_Registry::get('translate')->_('PRODUCTS_SEARCH_RESULTS_%s_FOR_%s'),
                    $numItens,
                    $filters['expr1']
                );
                $this->view->messageType = 'success';
            } else {
                $this->view->message = sprintf(
                    Zend_Registry::get('translate')->_('PRODUCTS_SEARCH_NOT_FOUND_FOR_%s'),
                    $filters['expr1']
                );
                $this->view->messageType = 'warning';
            }
        }

        $this->view->filters = $filters;
        $this->view->order = $orderField;
        $this->view->orderDir = $orderDir;
        $this->view->viewType = $viewType;
        $this->view->paginator = $paginator;
        $this->view->paginationLimit = $itemsPerPage;
        $this->view->products = $registersArray;
    }

    /**
     * Retorna caminho da imagem redimensionada.
     * Se a imagem redimensionada ainda não existe, é criada.
     *
     * @param  string $imageFileName O nome do arquivo da imagem original.
     * @param  int    $width A largura desejada da imagem.
     * @param  int    $height A altura desejada da imagem.
     * @return string O caminho da imagem redimensionada.
     */
    private function getResizedImagePath($imageFileName, $width, $height)
    {
        // Caminho de upload do sistema:
        $uploadDirectory = APPLICATION_PATH . '/../files/products/';

        // Caminho público da imagem:
        $imageDirectory = "/img/products/";

        // Caminho completo da imagem para acesso ao resource:
        $fullImageDirectory = dirname($_SERVER['SCRIPT_FILENAME']) . $imageDirectory;

        // Cria o diretório da imagem se ainda não existe.
        if (!file_exists($fullImageDirectory)) {
            mkdir($fullImageDirectory);
        }

        // O nome da imagem redimensionada:
        $resizedImageFileName = "{$width}x{$height}-{$imageFileName}";

        // Cria a imagem redimensionada, se ainda não existe:
        if (!file_exists($fullImageDirectory . $resizedImageFileName)) {
            $imageResizer = new STLib_Image_Resizer($uploadDirectory . $imageFileName);
            $imageResizer
                ->resizeTo($width, $height)
                ->saveAs($fullImageDirectory . $resizedImageFileName)
            ;
        }

        // Retorna o caminho da imagem redimensionada:
        return $imageDirectory . $resizedImageFileName;
    }

    /**
     * Informações sobre um determinado produto.
     */
    public function infoAction()
    {
        //TODO: Criar Zend_Form com formulário da view desta action.

        $productId = $this->getRequest()->getParam('id');

        $register = new Order_Model_Product();
        $register->find($productId);

        $this->view->product = (object) array(
            'id'          => $register->getId(),
            'name'        => $register->getName(),
            'description' => $register->getDescription(),
            'image'       => $this->getResizedImagePath(
                $register->getImage(),
                380,
                380
            ),
            'price'       => $register->getPrice(),
            'categories'  => $this->generateCategoriesTreeFor($register->getCategories()),
            'features'    => $register->getFeatures()
        );

        $this->view->quantity = 1;
        $this->view->isAtCart = false;
        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');
        if ($shoppingCart != null && $shoppingCart->productExists($productId)) {
            $this->view->quantity = $shoppingCart->getProductQuantity($productId);
            $this->view->isAtCart = true;
        }
        $this->view->maxQuantity = Zend_Registry::get('systemConfig')->products->maxquantity;
    }

    /**
     * Adicionar produto ao carrinho.
     */
    public function addtocartAction()
    {
        $params = $this->_request->getParams();
        $view = $this->_request->getParam('view', 'info');

        // Caso o usuário tenha vindo de um form de login:
        if (isset($params['params'])) {
            $params = $params['params'];
        }

        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');
        if ($shoppingCart == null) {
            $shoppingCart = new Order_Model_Order();
            $shoppingCart->setUserId(Zend_Registry::get('loggedInUser')->getId());
        }
        if (!isset($params['quantity']) || $params['quantity'] < 0) {
            $params['quantity'] = 1;
        }

        $maxQuantity = Zend_Registry::get('systemConfig')->products->maxquantity;
        if ($params['quantity'] > $maxQuantity) {
            $params['quantity'] = $maxQuantity;
        }

        $shoppingCart->setProduct($params['id'], $params['quantity']);

        Auth_Model_SystemAuth::getInstance()->setAuthVariable('shoppingCart', $shoppingCart);

        $this->_request->setParam('id', $params['id']);
        $this->_forward($view, 'products', 'order');
    }

    /**
     * Remover produto do carrinho.
     */
    public function removefromcartAction()
    {
        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');
        $productId = $this->_request->getParam('id');
        $view = $this->_request->getParam('view', 'index');

        if ($shoppingCart != null && $productId != null && $shoppingCart->productExists($productId)) {
            $shoppingCart->removeProduct($productId);
        }

        Auth_Model_SystemAuth::getInstance()->setAuthVariable('shoppingCart', $shoppingCart);

        $this->view->messageType = 'success';
        $this->view->message = Zend_Registry::get('translate')->_('PRODUCTS_ORDER_PRODUCT_REMOVED');

        $this->_forward($view, 'products', 'order');
    }

    /**
     * Confirmação do carrinho.
     */
    public function confirmcheckoutAction()
    {
        $this->view->maxQuantity = Zend_Registry::get('systemConfig')->products->maxquantity;
    }

    /**
     * Confirmação do endereço padrão do usuário e do
     * endereço de entrega.
     */
    public function confirmaddressesAction()
    {
        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');
        if ($shoppingCart == null || ($shoppingCart != null && count($shoppingCart->getProducts())) == 0) {
            $this->_helper->redirector('confirmcheckout', 'products', 'order');
        } else {

            $form = new Order_Form_UserAddresses();

            $userId = Zend_Registry::get('loggedInUser')->getId();
            $mainAddress = new Order_Model_UserAddress();
            $mainAddress->findMainAddressOf($userId);

            // Se o endereço principal ainda não existe:
            if ($mainAddress->getId() == null) {
                $mainAddress
                    ->setUserId($userId)
                    ->setMain(true)
                ;
            }

            if (!$this->_request->isPost()) {

                if ($mainAddress->getId() != null) {
                    $form->populate(array(
                        'personalStreet'       => $mainAddress->getStreet(),
                        'personalNumber'       => $mainAddress->getNumber(),
                        'personalComplement'   => $mainAddress->getComplement(),
                        'personalNeighborhood' => $mainAddress->getNeighborhood(),
                        'personalCity'         => $mainAddress->getCity(),
                        'personalState'        => $mainAddress->getState(),
                        'personalCep'          => $mainAddress->getCep()
                    ));
                }
            } else {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $mainAddress
                        ->setStreet($this->_request->getParam('personalStreet', $mainAddress->getStreet()))
                        ->setNumber((int) $this->_request->getParam('personalNumber', $mainAddress->getNumber()))
                        ->setComplement($this->_request->getParam('personalComplement', $mainAddress->getComplement()))
                        ->setNeighborhood($this->_request->getParam('personalNeighborhood', $mainAddress->getNeighborhood()))
                        ->setCity($this->_request->getParam('personalCity', $mainAddress->getCity()))
                        ->setState($this->_request->getParam('personalState', $mainAddress->getState()))
                        ->setCep($this->_request->getParam('personalCep', $mainAddress->getCep()))
                        ->save()
                    ;

                    if ($this->_request->getParam('sameAddress', false)) {
                        $shippingAddress = $mainAddress;
                    } else {
                        $shippingAddress = new Order_Model_UserAddress();
                        $shippingAddress
                            ->setUserId((int) $userId)
                            ->setMain(false)
                            ->setStreet($this->_request->getParam('shippingStreet'))
                            ->setNumber((int) $this->_request->getParam('shippingNumber'))
                            ->setComplement($this->_request->getParam('shippingComplement'))
                            ->setNeighborhood($this->_request->getParam('shippingNeighborhood'))
                            ->setCity($this->_request->getParam('shippingCity'))
                            ->setState($this->_request->getParam('shippingState'))
                            ->setCep($this->_request->getParam('shippingCep'))
                            ->save()
                        ;
                    }

                    $shoppingCart->setShippingAddressId($shippingAddress->getId());
                    Auth_Model_SystemAuth::getInstance()->setAuthVariable('shoppingCart', $shoppingCart);

                    $this->_helper->redirector('checkout', 'products', 'order');
                } else {
                    $form->populate($formData);
                }
            }

            $this->view->form = $form;
        }
    }

    /**
     * Finalização do pedido e envio de e-mail.
     */
    public function checkoutAction()
    {
        $shoppingCart = Auth_Model_SystemAuth::getInstance()->getAuthVariable('shoppingCart');
        if ($shoppingCart != null && count($shoppingCart->getProducts()) > 0) {

            $shoppingCart->save();
            $productsList = $shoppingCart->getProducts();

            $html = new Zend_View();
            $html->translator = Zend_Registry::get('translate');
            $html->username = Zend_Registry::get('loggedInUser')->getName();
            $html->mailTitle = Zend_Registry::get('translate')->_('CHECKOUT_MAIL_TITLE');
            $html->setScriptPath(APPLICATION_PATH . '/modules/order/views/emails/');
            $html->assign('products', $this->prepareShoppingCartProducts($productsList));

            $bodyText = $html->render('template.phtml');

            $mail = new Zend_Mail(Zend_Registry::get('systemConfig')->mail->encoding);
            $mail->setBodyHtml($bodyText);
            $mail->addTo(
                Zend_Registry::get('loggedInUser')->getEmail(),
                Zend_Registry::get('loggedInUser')->getName()
            );
            $mail->setSubject(Zend_Registry::get('translate')->_('CHECKOUT_MAIL_TITLE'));
            $mail->send();
            $this->view->messageType = 'success';
            $this->view->message = Zend_Registry::get('translate')->_('PRODUCTS_CHECKOUT_SUCCESS');

            Auth_Model_SystemAuth::getInstance()->setAuthVariable('shoppingCart', null);
        }

        $this->_forward('show', 'products', 'order');
    }
}
