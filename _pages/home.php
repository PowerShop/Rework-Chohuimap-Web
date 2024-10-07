<div class="container">
    <div class="card text-white" style="background-color: rgb(95, 124, 217);">
        <div class="card-body">
            <h3 class="mt-2 text-center"><i class="fa-solid fa-bag-shopping"></i> ซื้ออะไรดี?</h3>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="ค้นหาที่นี่" id="search_location">
            </div>
        </div>
    </div>

    <!-- Show Data Here -->
    <div class="row hidden" id="results"></div>

    <div id="no-data-found"></div>


    <!-- 3 Cards -->
    <div class="row mt-3" id="quick_search">
        <h3 class="text-start"><i class="fa-solid fa-magnifying-glass" style="--fa-bounce-land-scale-x: 1.05;--fa-bounce-land-scale-y: .8;--fa-bounce-rebound: 5px;"></i> ค้นหาด่วน</h3>
        <div class="col-md-2 mb-2">
            <div class="card border border-0 quick-search" data-keywords="ขนม,ของทานเล่น,อาหาร" style="background-color: rgb(63, 155, 241);">
                <div class="card-body text-center">
                    <a href="#" class="text-decoration-none text-white">
                        <img src="_dist/_img/food.png" class="img-fluid" alt="food" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-bowl-food"></i> อาหาร</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border border-0 quick-search" data-keywords="เครื่องดื่ม,เหล้า,เบียร์,แอลกอฮอร์,น้ำอัดลม,น้ำเปล่า" style="background-color: rgb(246, 162, 0);">
                <div class="card-body text-center">
                    <a href="#" class="text-decoration-none text-white">
                        <img src="_dist/_img/drink.png" class="img-fluid" alt="drink" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-wine-bottle"></i> เครื่องดื่ม</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border border-0 quick-search" data-keywords="ขนม" style="background-color: rgb(237, 39, 39);">
                <div class="card-body text-center">
                    <a href="#" class="text-decoration-none text-white">
                        <img src="_dist/_img/snack.png" class="img-fluid" alt="snack" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-cookie-bite"></i> ของทานเล่น</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border border-0 quick-search" data-keywords="ร้านยา,ยารักษาโรค,ยาพารา,ยาสามัญประจำบ้าน" style="background-color: rgba(0, 128, 0, 0.5);">
                <div class="card-body text-center">
                    <a href="#" class="text-decoration-none text-white">
                        <img src="_dist/_img/medicine.png" class="img-fluid" alt="medicine" width="64px" height="64px" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-pills"></i> ยารักษาโรค</h5>
        </div>
    </div>
</div>

<script>
    document.getElementById('search_location').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        searchStores(query);
    });

    document.querySelectorAll('.quick-search').forEach(card => {
        card.addEventListener('click', function() {
            const keywords = this.getAttribute('data-keywords').split(',');
            searchStores(keywords);
        });
    });

    function searchStores(query) {
        const resultsContainer = document.getElementById('results');
        const quickSearchContainer = document.getElementById('quick_search');
        const noDataFoundContainer = document.getElementById('no-data-found');

        if (query === '' || (Array.isArray(query) && query.length === 0)) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            quickSearchContainer.classList.remove('hidden');
            noDataFoundContainer.innerHTML = '';
            return;
        }

        // Show skeleton loading
        resultsContainer.innerHTML = `
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="skeleton" style="height: 200px;"></div>
                    <div class="card-body">
                        <h5 class="card-title skeleton" style="height: 20px; width: 60%;"></h5>
                        <p class="card-text skeleton" style="height: 15px; width: 80%;"></p>
                        <p class="card-text skeleton" style="height: 15px; width: 90%;"></p>
                        <p class="card-text skeleton" style="height: 15px; width: 70%;"></p>
                        <p class="card-text skeleton" style="height: 15px; width: 50%;"></p>
                        <p class="card-text skeleton" style="height: 15px; width: 60%;"></p>
                        <p class="card-text skeleton" style="height: 15px; width: 40%;"></p>
                        <hr>
                        <p class="card-text skeleton" style="height: 15px; width: 80%;"></p>
                    </div>
                </div>
            </div>
        `;
        resultsContainer.classList.remove('hidden');
        quickSearchContainer.classList.add('hidden');
        noDataFoundContainer.innerHTML = '';

        if (Array.isArray(query)) {
            const promises = query.map(keyword => fetchResults(keyword.trim()));
            Promise.all(promises).then(results => {
                const combinedResults = [].concat(...results);
                handleResults(combinedResults);
            });
        } else {
            fetchResults(query).then(data => {
                handleResults(data);
            });
        }
    }

    function fetchResults(query) {
        return fetch(`../_funcs/_search.php?query=${query}`)
            .then(response => response.json());
    }

    function handleResults(data) {
        const resultsContainer = document.getElementById('results');
        const noDataFoundContainer = document.getElementById('no-data-found');
        const quickSearchContainer = document.getElementById('quick_search');

        if (data.length === 0) {
            noDataFoundContainer.innerHTML = '<p class="text-center text-danger"><i class="fa-solid fa-exclamation-circle me-2"></i>ไม่พบข้อมูลร้านค้า</p>';
            resultsContainer.classList.add('hidden');
        } else {
            displayResults(data);
            resultsContainer.classList.remove('hidden');
            noDataFoundContainer.innerHTML = '';
        }
        quickSearchContainer.classList.add('hidden');
    }

    function displayResults(stores) {
        const resultsContainer = document.getElementById('results');
        resultsContainer.innerHTML = '';
        stores.forEach(store => {
            const keywords = JSON.parse(store.store_keywords).join(', ');
            const card = `
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="${store.store_image}" class="card-img-top rounded-top" alt="${store.store_name}" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><i class="fa-solid fa-store me-2"></i>${store.store_name}</h5>
                                <p class="card-text text-muted"><i class="fa-solid fa-info-circle me-2"></i>${store.store_description}</p>
                                <p class="card-text"><i class="fa-solid fa-map-marker-alt text-danger me-2"></i>${store.store_address}</p>
                                <p class="card-text"><i class="fa-solid fa-phone text-success me-2"></i>${store.store_phone}</p>
                                <p class="card-text"><i class="fa-solid fa-user text-info me-2"></i>${store.store_author}</p>
                                <p class="card-text"><i class="fa-solid fa-clock text-warning me-2"></i>เปิด: ${store.store_open} - ปิด: ${store.store_close}</p>
                                <p class="card-text"><i class="fa-solid fa-star text-warning me-2"></i>คะแนน: ${store.store_rating}</p>
                                <hr>
                                <p class="card-text"><i class="fa-solid fa-tags text-secondary me-2"></i>คำสำคัญ: ${keywords}</p>
                                <hr>
                                <div class="text-center">
                                    <div class="row>
                                        <div class="col-md-12">
                                            <a href="?page=map&storename=${store.store_name}&lat=${store.store_lat}&lon=${store.store_lon}" class="btn btn-primary mb-1 text-white col-md-4"><i class="fa-solid fa-route me-2"></i>เดินทาง</a>
                                            <a href="tel:${store.store_phone}" class="btn btn-success mb-1 text-white col-md-4"><i class="fa-solid fa-phone me-2"></i>โทร</a>
                                            <button class="btn btn-info mb-1 text-white col-md-4 infooo" data-store-name="${store.store_name}" data-store-info="นี่คือรายละเอียดเกี่ยวกับ ${store.store_name}"><i class="fa-solid fa-info-circle me-2"></i>รายละเอียด</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            resultsContainer.innerHTML += card;
        });
    }
</script>

<!-- SweetAlert2 -->
<!-- เมื่อกดที่ปุ่ม Info ของร้านค้า จะเปิดหน้า sweet alert ที่มีข้อมูลเพิ่มเติมของร้านค้า -->
<script>
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('infooo')) {
            const button = event.target;
            const storeName = button.getAttribute('data-store-name');
            const storeInfo = button.getAttribute('data-store-info');

            Swal.fire({
                title: storeName,
                text: storeInfo,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }
    });
</script>

<!-- Make Javascript to random ร้านค้าขึ้นมาแนะนำตรงมุมของหน้าจอ -->
</div>