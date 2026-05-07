<!-- Hero Section -->
<section class="hero">
    <div class="container hero__inner">
        <div class="hero__text">
            <h1 class="hero__title">Find Your <span class="highlight">Dream Property</span></h1>
            <p class="hero__subtitle">Thousands of verified listings across Bangladesh. Buy, sell, or rent — we make it simple.</p>

            <!-- Search Form -->
            <form class="search-form" action="/properties" method="GET">
                <div class="search-form__grid">
                    <select name="type" class="form-control">
                        <option value="">Property Type</option>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="commercial">Commercial</option>
                        <option value="land">Land</option>
                    </select>

                    <select name="status" class="form-control">
                        <option value="">For Sale / Rent</option>
                        <option value="sale">For Sale</option>
                        <option value="rent">For Rent</option>
                    </select>

                    <input type="text" name="location" class="form-control" placeholder="Enter location...">

                    <button type="submit" class="btn btn--primary">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <div class="hero__stats">
            <div class="stat-card">
                <div class="stat-card__number">5,000+</div>
                <div class="stat-card__label">Properties</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__number">1,200+</div>
                <div class="stat-card__label">Agents</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__number">3,800+</div>
                <div class="stat-card__label">Happy Clients</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Placeholder -->
<section class="section section--featured">
    <div class="container">
        <h2 class="section__title">Featured Properties</h2>
        <p class="section__subtitle">Hand-picked listings just for you</p>
        <div class="property-grid">
            <!-- Properties will be populated dynamically in Phase 3 -->
            <div class="property-card property-card--placeholder">
                <div class="placeholder-box" style="height:200px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;">
                    <i class="fa-solid fa-image fa-2x"></i>
                </div>
                <div class="property-card__body">
                    <p style="color:#aaa;text-align:center;margin-top:1rem;">Properties module coming in Phase 3</p>
                </div>
            </div>
        </div>
    </div>
</section>
