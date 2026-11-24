document.addEventListener('DOMContentLoaded', function () {

    console.log("its load from premium template js");
    const apiEndpoint = ediot_ajax_template_object.plugin_url + '/fetch-api.php';
    let endCursor = null;
    let searchQuery = '';
    let selectedCollection = '';
    let debounceTimeout = null; // To store debounce timeout

    function showLoader(loaderId) {
        const loader = document.getElementById(loaderId);
        if (loader) loader.style.display = 'block';
    }

    function hideLoader(loaderId) {
        const loader = document.getElementById(loaderId);
        if (loader) loader.style.display = 'none';
    }

function fetchCollections() {
        showLoader('categories-loader');
        return fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getCollections', wpnonce: ediot_ajax_template_object.wpnonce })
        })
            .then(response => response.json())
            .then(data => {
                populateDropdown(data);
                hideLoader('categories-loader');
            })
            .catch(error => {
                console.error('Error fetching collections:', error);
                hideLoader('categories-loader');
            });
    }

function fetchApiData(afterCursor = null, append = false) {
        showLoader('products-loader');
        return fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'getProducts',
                wpnonce: ediot_ajax_template_object.wpnonce,
                collectionHandle: selectedCollection,
                productHandle: searchQuery,
                paginationParams: { first: 12, afterCursor: afterCursor, reverse: true }
            })
        })
            .then(response => response.json())
            .then(data => {
                displayData(data, append);
                endCursor = data.data.pageInfo.endCursor;
                document.getElementById('load-more').style.display = data.data.pageInfo.hasNextPage ? 'block' : 'none';
                hideLoader('products-loader');
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                hideLoader('products-loader');
            });
    }


    function populateDropdown(data) {
        const listContainer = document.getElementById('api-response-list');
        listContainer.innerHTML = '';

        listContainer.innerHTML += `
            <li class="list-group-item list-group-item-action active" data-value="">
                All Categories
            </li>
        `;

        if (data && data.data && Array.isArray(data.data)) {
            data.data
                .filter((collection) => collection.title.toLowerCase() !== 'uncategorized' || collection.title.toLowerCase() !== 'testing' )
                .sort((a, b) => a.title.localeCompare(b.title))
                .forEach((collection) => {
                    listContainer.innerHTML += `
            <li class="list-group-item list-group-item-action" data-value="${collection.handle}">
                ${collection.title}
            </li>
        `;
                });
        } else {
            console.error('Data format is incorrect:', data);
        }
    }

    function displayData(data, append = false) {
        const productCards = document.getElementById('product-cards');
        if (!append) productCards.innerHTML = '';

        if (data && data.data && Array.isArray(data.data.products)) {
            let filteredProducts = data.data.products;

            if (searchQuery) {
                filteredProducts = filteredProducts.filter(product => product.node.inCollection === true);
            }

            filteredProducts.forEach(product => {
                const item = product.node;
                const imageSrc = item.images.edges[0]?.node.src || 'default-image.jpg';
                const price = item.variants.edges[0]?.node.price || 'N/A';
                const demoLink = item.metafield?.value || '#';

                const colElement = document.createElement('div');
                colElement.classList.add('col-12', 'col-md-12', 'col-lg-12', 'col-xl-6', 'col-xxl-4', 'mb-3');
                colElement.innerHTML = `
                    <div class="card">
                        <div class="card-img-wrap">
                            <img src="${imageSrc}" class="card-img-top" alt="${item.title}">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="card-text"><span>Price: </span>$${price}</p>

                            <div class="ov-button-wrapper"><a href="${item.onlineStoreUrl}" class="btn btn-primary" target="_blank">Buy Now</a>
                            <a href="${demoLink}" class="btn btn-primary demo" target="_blank" style="margin-left: 10px;">Demo</a></div>
                        </div>
                    </div>
                `;
                productCards.appendChild(colElement);
            });
        } else {
            console.error('Data format is incorrect:', data);
        }
    }

    function debouncedSearch() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(function () {
            endCursor = null;
            fetchApiData();
        }, 1000);
    }

    const searchBox = document.getElementById('search-box');

    if (searchBox) {
        searchBox.addEventListener('input', function () {
            searchQuery = this.value.trim();
            debouncedSearch();
        });
    }

    const loadMoreBtn = document.getElementById('load-more');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            if (endCursor) {
                fetchApiData(endCursor, true);
            }
        });
    }


    const apiResponseList = document.getElementById('api-response-list');

    if (apiResponseList) {
        apiResponseList.addEventListener('click', function (e) {
            const clickedItem = e.target;

            if (clickedItem && clickedItem.matches('li.list-group-item')) {
                document.querySelectorAll('#api-response-list li')
                    .forEach(item => item.classList.remove('active'));

                clickedItem.classList.add('active');

                selectedCollection = clickedItem.getAttribute('data-value') || '';
                endCursor = null;

                fetchApiData();
            }
        });
    }



    fetchCollections();
    fetchApiData();
});
