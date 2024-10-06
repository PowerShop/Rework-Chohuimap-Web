<div class="container">
    <div class="card text-white" style="background-color: rgb(95, 124, 217);">
        <div class="card-body">
            <h3 class="mt-2 text-center"><i class="fa-solid fa-bag-shopping"></i> ซื้ออะไรดี?</h3>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="ค้นหาที่นี่" id="search_location">
                <!-- <button class="btn btn-primary" onclick="search();"><i class="fa fa-search" aria-hidden="true"></i> ค้นหา</button> -->
            </div>
        </div>
    </div>

    <div id="no-data-found"></div>
    <!-- 3 Cards -->
    <div class="row mt-3" id="quick_search">

        <h3 class="text-start"><i class="fa-solid fa-magnifying-glass" style="--fa-bounce-land-scale-x: 1.05;--fa-bounce-land-scale-y: .8;--fa-bounce-rebound: 5px;"></i> ค้นหาด่วน</h3>
        <div class="col-md-2 mb-2">
            <div class="card border border-0" style="background-color: rgb(63, 155, 241);">
                <div class="card-body text-center">
                    <a href="?quick=food" class="text-decoration-none text-white">
                        <img src="_dist/_img/food.png" class="img-fluid" alt="food" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-bowl-food"></i> อาหาร</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border border-0" style="background-color: rgb(246, 162, 0);">
                <div class="card-body text-center">
                    <a href="?quick=drink" class="text-decoration-none text-white">
                        <img src="_dist/_img/drink.png" class="img-fluid" alt="food" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-wine-bottle"></i> เครื่องดื่ม</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border border-0" style="background-color: rgb(237, 39, 39);">
                <div class="card-body text-center">
                    <a href="?quick=snack" class="text-decoration-none text-white">
                        <img src="_dist/_img/snack.png" class="img-fluid" alt="food" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-cookie-bite"></i> ของทานเล่น</h5>
        </div>

        <div class="col-md-2 mb-2">
            <div class="card border bordeer-0" style="background-color: rgba(0, 128, 0, 0.5);">
                <div class="card-body text-center">
                    <a href="?quick=medicine" class="text-decoration-none text-white">
                        <img src="_dist/_img/medicine.png" class="img-fluid" alt="medicine" width="64px" height="64px" />
                    </a>
                </div>
            </div>
            <h5 class="mt-2"><i class="fa-solid fa-pills"></i> ยารักษาโรค</h5>
        </div>
    </div>
</div>