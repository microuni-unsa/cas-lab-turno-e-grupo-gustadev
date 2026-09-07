'use strict';

/**
 * Connection visualization, built with d3 (v3).
 *
 * Use like:
 * new window.ConnectionVisualization($visualization, {...)})
 *    .setData([[0, 1, 'master'], [5, 3, 'slave'], [2, 4, null]])
 *    .update();
 *
 * The data contains three pieces of information:
 *  - source (index)
 *  - target (index)
 *  - relation mode ('master', 'slave' or null)
 *
 *  According to the "relation mode" the cable will be drawn from left to right or right to left.
 *
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

window.ConnectionVisualization = Class.create({
    $root: null,

    initialize: function ($root, options) {
        this.$root = $root;

        this.options = {
            width:                this.$root.getWidth(),
            height:               this.$root.getHeight(),
            itemHeight:           40,
            permanentLocal:       null,
            permanentDestination: null
        };

        Object.extend(this.options, options || {});

        this.svg = d3.select(this.$root).append('svg')
                     .attr('width', this.options.width)
                     .attr('height', this.options.height);

        this.svg.append('style').attr('type', 'text/css').text(
            '.arrow { fill: #e0e0e0 !important; stroke: #fff !important; stroke-width: 1px !important; } ' +
            '.arrow-highlight { fill: #36a9f7 !important; stroke: #fff !important; stroke-width: 2px !important; }'
        );

        const $defs = this.svg.append('defs');

        $defs
            .append('marker')
            .attr('id', 'arrow')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 0)
            .attr('refY', 0)
            .attr('markerWidth', 8)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5')
            .attr('class', 'arrow');

        $defs
            .append('marker')
            .attr('id', 'arrow-highlight')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 0)
            .attr('refY', 0)
            .attr('markerWidth', 8)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5')
            .attr('class', 'arrow-highlight');

        this.vis = this.svg.append('g');
    },

    setData: function (data) {
        this.data = data;

        return this;
    },

    update: function () {
        var opts = this.options,
            selection,
            data = [];

        for (let i in this.data) {
            if (!this.data.hasOwnProperty(i)) {
                continue;
            }

            // The structure now represents ['source', 'target', 'mode'] where the mode can be either null, 'master' or 'slave' (which always relates to the 'source')
            data.push([this.data[i][0], this.data[i][1], this.data[i][2]]);
        }

        selection = this.vis.selectAll('path').data(data);

        selection.enter()
            .append('path')
            .attr('data-source', (d) => d[0])
            .attr('data-target', (d) => d[1])
            .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow)' : null)
            .attr('d', function (d) {
                const rtl = d[2] === 'slave';
                const padding = d[2] === null ? 0 : 13

                const sourceX = rtl ? opts.width : 0;
                const sourceY = opts.itemHeight + (d[rtl ? 1 : 0] * opts.itemHeight) - (opts.itemHeight / 2);

                const targetX = rtl ? padding : (opts.width - padding);
                const targetY = opts.itemHeight + (d[rtl ? 0 : 1] * opts.itemHeight) - (opts.itemHeight / 2);

                return 'M' + sourceX + ',' + sourceY + 'L' + ((sourceX + targetX) / 2) + ',' + ((sourceY + targetY) / 2) + 'L' + targetX + ',' + targetY;
            });

        selection.exit()
                 .remove();
    },

    reset: function () {
        this.data = [];

        this.update();
    },

    highlight: function (local, destination) {
        if (local === null && destination === null)
        {
            this.vis.selectAll('path')
                .classed('opacity-30', false)
                .classed('highlight', false)
                .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow)' : null);
        }

        if (local !== null)
        {
            this.vis.select('path[data-source="' + local + '"]')
                .classed('highlight', true)
                .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow-highlight)' : null);
            this.vis.selectAll('path:not([data-source="' + local + '"])').classed('opacity-30', true);
        }

        if (this.options.permanentLocal !== null)
        {
            this.vis.select('path[data-source="' + this.options.permanentLocal + '"]')
                .classed('highlight', true)
                .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow-highlight)' : null)
                .classed('opacity-30', false);
        }

        if (destination !== null)
        {
            this.vis.select('path[data-target="' + destination + '"]')
                .classed('highlight', true)
                .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow-highlight)' : null);
            this.vis.selectAll('path:not([data-target="' + destination + '"])')
                .classed('opacity-30', true);
        }

        if (this.options.permanentDestination !== null)
        {
            this.vis.select('path[data-target="' + this.options.permanentDestination + '"]')
                .classed('highlight', true)
                .classed('opacity-30', false)
                .attr('marker-end', (d) => d[2] !== null ? 'url(#arrow-highlight)' : null);
        }
    },

    highlightPermanent: function (local, destination) {
        this.options.permanentLocal = local;
        this.options.permanentDestination = destination;
    },

    updateDimension: function (width, height) {
        if (width !== null)
        {
            this.options.width = width;
        }

        if (height !== null)
        {
            this.options.height = height;
        }

        d3.select(this.$root).select('svg')
          .attr('width', this.options.width)
          .attr('height', this.options.height);
    }
});
