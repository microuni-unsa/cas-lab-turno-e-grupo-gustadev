[{if $rss->get_image_url()}]
    <div class="fr">
        <img src="[{$rss->get_image_url()}]" height="16" />
    </div>
[{/if}]

<h2>
    <a href="[{$rss->get_permalink()}]" target="_blank">[{$rss->get_title()}]</a>: [{$rss->get_description()}]
</h2>

<hr class="mt10 cb" />

[{foreach $items as $item}]
<div class="pt10">
    <p><strong><a href="[{$item.url}]" target="_blank">[{$item.title}]</a></strong></p>
    <p>[{$item.description}]</p>
    <p class="text-neutral-600 text-right">[{isys type="lang" ident="LC__WIDGET__RSS__POSTED_ON"}] [{$item.date}]</p>
</div>
[{/foreach}]
