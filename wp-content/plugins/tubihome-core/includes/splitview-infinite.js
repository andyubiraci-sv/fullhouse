jQuery(function($){
    var page = 2;
    var loading = false;
    var done = false;
    var $grid = $('#inmuebles-grilla');
    var term = $grid.data('term') || '';
    var operacion = $grid.data('operacion') || '';

    // Toast

    // Esperar a que el DOM esté listo y el body exista
    function ensureModal(){
        if($('#splitview-modal').length) return $('#splitview-modal');
        var $modal = $('<div id="splitview-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:999999;justify-content:center;align-items:center;"><div style="background:#fff;padding:32px 48px;border-radius:12px;box-shadow:0 2px 24px #0003;display:flex;flex-direction:column;align-items:center;gap:18px;"><div class="splitview-spinner" style="width:48px;height:48px;border:6px solid #eee;border-top:6px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;"></div><div style="font-size:1.2em;color:#222;">Cargando más propiedades...</div></div></div>');
        // Spinner CSS
        var style = document.getElementById('splitview-spinner-style');
        if(!style){
            style = document.createElement('style');
            style.id = 'splitview-spinner-style';
            style.innerHTML = '@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}';
            document.head.appendChild(style);
        }
        $('body').append($modal);
        return $modal;
    }
    var $modal = ensureModal();

    // Log para depuración
    window.splitviewModalTest = function(){
        var $m = ensureModal();
        $m.fadeIn(200);
        setTimeout(function(){$m.fadeOut(200);}, 2000);
    };

    function showModal() {
        var $m = ensureModal();
        $m.fadeIn(200);
    }
    function hideModal() {
        var $m = ensureModal();
        $m.fadeOut(200);
    }

    function loadMore(){
        if(loading || done) return;
        loading = true;
        showModal();
        $.ajax({
            url: tubihome_splitview.ajaxurl,
            type: 'POST',
            data: {
                action: 'tubihome_splitview_load',
                paged: page,
                term: term,
                operacion: operacion
            },
            success: function(html){
                if($.trim(html)===''){
                    done = true;
                    hideModal();
                }else{
                    $grid.append(html);
                    hideModal();
                    page++;
                }
                loading = false;
            },
            error: function(){
                hideModal();
                loading = false;
            }
        });
    }
    $(window).on('scroll', function(){
        if(done) return;
        if($(window).scrollTop() + $(window).height() > $grid.offset().top + $grid.height() - 200){
            loadMore();
        }
    });
});
