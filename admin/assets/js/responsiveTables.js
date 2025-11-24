(function () {
    function wrapTable(table) {
        if (
            table.parentElement &&
            table.parentElement.classList.contains('responsive-table-wrapper')
        ) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'responsive-table-wrapper';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    }

    function getHeaders(table) {
        const headers = [];
        const headerCells = table.querySelectorAll('thead th');
        if (headerCells.length === 0) {
            return headers;
        }

        headerCells.forEach((th) => {
            headers.push(th.textContent.trim());
        });

        return headers;
    }

    function setDataLabels(table) {
        const headers = getHeaders(table);
        if (headers.length === 0) return;

        table.querySelectorAll('tbody tr').forEach((row) => {
            row.querySelectorAll('td').forEach((cell, index) => {
                if (!cell.hasAttribute('data-label') && headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
    }

    function enhanceTable(table) {
        if (!table || table.dataset.staticTable === 'true') return;

        table.classList.add('responsive-table');
        wrapTable(table);
        setDataLabels(table);
        observeTable(table);
    }

    function observeTable(table) {
        if (table.dataset.responsiveObserved === 'true') return;

        const observer = new MutationObserver(() => setDataLabels(table));

        Array.from(table.tBodies).forEach((tbody) => {
            observer.observe(tbody, { childList: true, subtree: true });
        });

        table.dataset.responsiveObserved = 'true';
    }

    function initResponsiveTables() {
        document
            .querySelectorAll('table')
            .forEach((table) => enhanceTable(table));
    }

    function observeNewTables() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;

                    if (node.tagName === 'TABLE') {
                        enhanceTable(node);
                    } else if (node.querySelectorAll) {
                        node.querySelectorAll('table').forEach((table) => enhanceTable(table));
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initResponsiveTables();
        observeNewTables();
    });

    window.hampcoRefreshResponsiveTables = function () {
        initResponsiveTables();
    };
})();

