$(document).ready(function() {

    // Aplica select customizado para o Twitter Bootstrap:
    $('.selectpicker').selectpicker();

    // Todal de itens do carrinho:
    var numCartItems = 0;
    $('#cart-content .media span.quantity').each(function(index, element) {
        numCartItems += parseInt($(element).text());
    });
    $('#cart .order-lenght').html('(' + numCartItems + ')');

    // Manuseio do menu como árvore:
    $('label .tree-toggler').click(function() {
        $(this).parent().parent().children('ul.tree').toggle(300);
        if ($(this).hasClass('fa-toggle-right')) {
            $(this).removeClass('fa-toggle-right');
            $(this).addClass('fa-toggle-down');
        } else {
            $(this).removeClass('fa-toggle-down');
            $(this).addClass('fa-toggle-right');
        }
    });

    // Mudança de quantidade de itens no carrinho:
    $(".quantity-change").change(function() {
        var form = $('#addToCartForm');
        form.find('input[name=id]').val($(this).attr('data-product-id'));
        form.find('input[name=quantity]').val($(this).val());

        form.submit();
    });

    // Comportamento da busca:
    $('.form-search input').keydown(function(keyEvent) {
        if (keyEvent.keyCode == 13) {// <- enter
            $('.form-search button').trigger('click');
        }
    });
    $('.form-search button').click(function() {
        var form = $('form[name=hidden-search-form]');
        form.find('input[name=search]').val($('#searchbar').val());
        form.submit();
    });

    // Filtros e ordenação:
    $('#orderSelect').change(function() {
        var orderInfo = $(this).val().split("_");
        $('#order').val(orderInfo[0]);
        $('#orderdir').val(orderInfo[1]);
        submitProductViewForm();
    });

    // Limite de itens por página:
    $('#limitSelect').change(function() {
        submitProductViewForm();
    });

    // Simula links no breadcrumb dentro do ambiente "media":
    $('.media .breadcrumb span').click(function(event) {
        event.stopPropagation();
        event.preventDefault();
        $(location).attr('href', $(this).attr('data-link'));
    });

    // Evita que o popup com form de login feche quando o usuário
    // cliar no formulário para preenchê-lo:
    $('.dropdown-menu>form').click(function(e) {
        e.stopPropagation();
    });

    // Submete form de adição ao carrinho com clique de botão:
    $('#addToCartButton').on("click", function() {
        $('#addToCartForm').submit();
    });

    // Formulário de finalização de pedido:
    $('#useraddressesform').submit(function() {
        $(this).find('select[disabled]').prop('disabled', false);
    });

    // Iguala cada campo dos formulários de entrega e principal, se necessário:
    $('#fieldset-personal input').keyup(function() {
        matchField(this);
    });
    $('#fieldset-personal select').change(function() {
        matchField(this);
    });

    // Iguala os formulários de entrega e principal quando a página é
    // carregada pela primeira vez e quando o usuário marca o checkbox:
    matchAddresses(false);
    $('#sameAddress').on("click", function() {
        matchAddresses(true);
    });

    // Permite apenas números e algumas outras teclas nos campos a seguir:
    $('#personalNumber').keydown(function(keyEvent) {
        return onlyNumbers(keyEvent);
    });
    $('#shippingNumber').keydown(function(keyEvent) {
        return onlyNumbers(keyEvent);
    });
    $('#personalCep').keydown(function(keyEvent) {
        return onlyNumbers(keyEvent, [ 109, 189 ]); // <- hífen
    });
    $('#shippingCep').keydown(function(keyEvent) {
        return onlyNumbers(keyEvent, [ 109, 189 ]); // <- hífen
    });

    // Suporte básico ao Twitter Bootstrap 3 nos formulários do EasyBib
    $('form select').addClass('form-control');
    $('form input[type=text]').addClass('form-control');
    $('form input[type=password]').addClass('form-control');

    // Muda modo visão dos produtos:
    $('.view-type-change button').click(function(keyEvent) {
        $('.view-type-change button').removeClass('active');
        $(this).addClass('active');
        $('input[name=viewtype]').val($(this).attr('data-view-type'));
        submitProductViewForm();
    });
});

/**
 * Iguala o endereço de entrega ao endereço principal do usário.
 */
function matchAddresses(emptyShippingForm) {
    if ($('#fieldset-sameaddressgroup').size() > 0) {
        if ($('#fieldset-sameaddressgroup input[type=checkbox]').get(0).checked) {

            $('#fieldset-personal input[type=text]').each(
                    function(index, element) {
                        matchField(element);
                    });
            $('#fieldset-personal select').each(function(index, element) {
                matchField(element);
            });
        } else {
            if (emptyShippingForm) {
                $('#fieldset-shipping input[type=text]')
                        .prop('readonly', false).val('');
                $('#fieldset-shipping select').prop('disabled', false).val('');
            }
        }
    }
}

/**
 * Iguala um elemento do form de entrega a seu similar indicado.
 * 
 * @param element
 *            O elemento indicado.
 */
function matchField(element) {
    if ($('#fieldset-sameaddressgroup input[type=checkbox]').get(0).checked) {
        var shippingElementId = $(element).attr('id').replace('personal',
                'shipping');
        var shippingElement = $('#' + shippingElementId);

        shippingElement.val($(element).val());
        shippingElement.prop('readonly', true);

        if (shippingElement.is('select')) {
            $('.selectpicker').selectpicker('render');

            // selects não tem a propriedade "readonly":
            shippingElement.prop('disabled', true);
        }
    }
}

/**
 * Bloqueia o uso de tecla que não são números e outras poucas excessões.
 * 
 * @param keyEvent
 *            O evento do teclado.
 * @param extraKeycodesToIgnore
 *            Lista de keyCodes extra a ignorar.
 */
function onlyNumbers(keyEvent, extraKeycodesToIgnore) {
    // backspace, enter, delete, tab, F5
    // home, end, left, right
    if ($.inArray(keyEvent.keyCode, [ 8, 13, 46, 116, 9 ]) !== -1
            || (extraKeycodesToIgnore != undefined && $.inArray(
                    keyEvent.keyCode, extraKeycodesToIgnore) !== -1)
            || (keyEvent.keyCode >= 35 && keyEvent.keyCode <= 39)) {
        return;
    }

    if ((keyEvent.shiftKey || (keyEvent.keyCode < 48 || keyEvent.keyCode > 57))
            && (keyEvent.keyCode < 96 || keyEvent.keyCode > 105)) {
        keyEvent.preventDefault();
    }
}

/**
 * Muda a página da paginação de produtos.
 * 
 * @param pageNumber
 *            O número da página de destino.
 */
function goToPage(pageNumber) {
    $('#page').val(pageNumber);
    submitProductViewForm();
}

/**
 * Submete o form de lista de produtos.
 */
function submitProductViewForm() {
    $('#productViewForm').submit();
}
