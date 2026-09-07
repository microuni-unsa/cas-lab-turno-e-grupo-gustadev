<input type="hidden" id="identificator_search" value="0" />

[{if ($tts_processing_error)}]
<div class="box-red p5">
    <p>[{$tts_processing_error}]</p>
</div>
[{else}]
    [{if $createTicketsTemplate}]
        [{include file="$createTicketsTemplate"}]
    [{/if}]

    [{if is_array($workstation) && $workstationTemplate}]
        [{include file="$workstationTemplate"}]
    [{elseif $objectTemplate}]
        [{include file="$objectTemplate"}]
    [{/if}]

[{/if}]

<script type="text/javascript">
    (function () {
        'use strict';

        const $table = $("tickets_table");

        if ($table) {
            $table.on('click', 'tr[data-trigger]', function (ev) {
                const $tr = $(ev.findElement('tr').readAttribute('data-trigger'));

                if ($tr) {
                    $tr.toggleClassName('hide');
                }
            });

            $table.on('click', 'th', function (ev) {
                var switching,
                    currentRowIndex,
                    currentRowDetailsIndex,
                    nextRowIndex,
                    nextRowDetailsIndex,
                    currentRow,
                    nextRow,
                    currentValue,
                    nextValue,
                    selectedColumn,
                    shouldSwitch,
                    dir,
                    switchcount = 0;

                switching = true;
                dir = 'desc';
                while (switching) {
                    switching = false;
                    const rows = $table.select('tr');

                    if (rows.length === 0) {
                        break;
                    }

                    for (currentRowIndex = 1; currentRowIndex < rows.length; currentRowIndex++) {
                        currentRowDetailsIndex = currentRowIndex + 1;
                        nextRowIndex = currentRowIndex + 2;
                        nextRowDetailsIndex = currentRowIndex + 3;
                        currentRow = rows[currentRowIndex];
                        nextRow = rows[nextRowIndex];
                        if (!currentRow.hasClassName('listRow') || nextRow == null) {
                            continue;
                        }
                        currentValue = currentRow.getElementsByTagName('TD')[ev.target.cellIndex].readAttribute('data-sort').toLowerCase();
                        nextValue = nextRow.getElementsByTagName('TD')[ev.target.cellIndex].readAttribute('data-sort').toLowerCase();
                        if ((dir === 'desc' && currentValue < nextValue) || (dir === 'asc' && currentValue > nextValue)) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                    if (shouldSwitch && rows[currentRowIndex + 3]) {
                        rows[currentRowIndex].parentNode.insertBefore(rows[nextRowIndex], rows[currentRowIndex]);
                        rows[currentRowIndex].parentNode.insertBefore(rows[nextRowDetailsIndex], rows[currentRowDetailsIndex]);
                        switching = true;
                        switchcount++;
                    } else if (switchcount === 0 && dir === "desc") {
                        dir = "asc";
                        switching = true;
                    }
                }

                selectedColumn = $table.getElementsByClassName('selectedColumn');
                if (selectedColumn.length > 0) {
                    selectedColumn[0].removeClassName('selectedColumn').removeClassName('desc').removeClassName('asc');
                }

                switch (dir) {
                    case 'asc':
                        ev.target.addClassName('asc').addClassName('selectedColumn');
                        break;
                    case 'desc':
                        ev.target.addClassName('desc').addClassName('selectedColumn');
                        break;
                }
            });
        }
    })();
</script>
