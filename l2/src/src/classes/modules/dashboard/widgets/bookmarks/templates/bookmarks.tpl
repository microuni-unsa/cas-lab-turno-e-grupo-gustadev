[{if is_array($tabledata)}]
<ol class="rectangle-list m0">
    [{foreach $tabledata as $bookmark}]
        <li>
            <a [{if $bookmark.new_window}]target="_blank"[{/if}] href="[{$bookmark.link}]">
                <span>[{$bookmark.title}]</span>
            </a>
        </li>
    [{/foreach}]
</ol>
[{/if}]

<style>
    ol.rectangle-list {
        counter-reset: li;
        list-style: none;
        padding: 0;
    }

    ol.rectangle-list ol {
        margin: 0 0 0 2em;
    }

    .rectangle-list a {
        position: relative;
        display: block;
		padding: .3em .4em .3em .8em;
		margin: .5em 0 .5em 2.5em;
        background: #ddd;
        color: #000;
        text-decoration: none;
        transition: all .2s ease-out;
		outline:1px solid #808080;
		border:1px solid #fff;
    }
    .rectangle-list a span {
        text-overflow: ellipsis;
        overflow: hidden;
        display: block;
    }

    .rectangle-list a:hover {
        background: #eee;
    }

    .rectangle-list a:before {
        content: counter(li);
        counter-increment: li;
        position: absolute;
		left: -2.6em;
		top: 50%;
		margin-top: -1em;
        background: #808080;
        color: #fff;
		height: 2em;
		width: 2em;
		line-height: 2em;
        text-align: center;
        font-weight: bold;
    }

    .rectangle-list a:after {
        position: absolute;
        content: '';
        border: .5em solid transparent;
        left: -1em;
        top: 50%;
        margin-top: -.5em;
        transition: all .2s ease-out;
    }

    .rectangle-list a:hover:after {
        left: -.6em;
        border-left-color: #808080;
    }
</style>
