(function($){
    var usedSlugs = {};

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-');
    }

    function processHeadings() {
        $('#content h1, #content h2, #content h3, #content h4').each(function(){
            var $el = $(this);
            if ($el.attr('id')) return;
            var base = slugify($el.text());
            var slug = base;
            var i = 1;
            while (usedSlugs[slug]) {
                slug = base + '-' + i++;
            }
            usedSlugs[slug] = true;
            $el.attr('id', slug);
        });
    }

    $(document).ready(processHeadings);
    $(document).ajaxComplete(processHeadings);

    // opcjonalnie: MutationObserver, gdy AJAX nie używa jQuery
    var observer = new MutationObserver(processHeadings);
    var container = document.getElementById('content');
    if (container) {
        observer.observe(container, { childList: true, subtree: true });
    }
})(jQuery);
