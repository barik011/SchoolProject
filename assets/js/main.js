(() => {
    const root = document.documentElement;
    const toggleButton = document.getElementById("themeToggle");
    const key = "schoolTheme";

    const applyTheme = (theme) => {
        root.setAttribute("data-bs-theme", theme);

        if (toggleButton) {
            const icon = toggleButton.querySelector("i");
            const text = toggleButton.querySelector("span");
            if (icon) {
                icon.className = theme === "dark" ? "bi bi-sun" : "bi bi-moon-stars";
            }
            if (text) {
                text.textContent = theme === "dark" ? "Light" : "Dark";
            }
        }
    };

    const savedTheme = localStorage.getItem(key);
    const initialTheme = savedTheme || root.getAttribute("data-bs-theme") || "light";
    applyTheme(initialTheme);

    if (toggleButton) {
        toggleButton.addEventListener("click", () => {
            const current = root.getAttribute("data-bs-theme") || "light";
            const next = current === "dark" ? "light" : "dark";
            localStorage.setItem(key, next);
            applyTheme(next);
        });
    }

    document.querySelectorAll("form.needs-validation").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add("was-validated");
        });
    });

    document.querySelectorAll("[data-confirm]").forEach((element) => {
        element.addEventListener("click", (event) => {
            const text = element.getAttribute("data-confirm") || "Are you sure?";
            if (!window.confirm(text)) {
                event.preventDefault();
            }
        });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 }
    );

    document.querySelectorAll(".reveal").forEach((item) => observer.observe(item));

    const bannerCarousel = document.getElementById("homeBannerCarousel");
    if (bannerCarousel) {
        const activateBannerCaption = () => {
            bannerCarousel.querySelectorAll(".home-banner-content").forEach((content) => {
                content.classList.remove("is-visible");
            });

            const activeSlide = bannerCarousel.querySelector(".carousel-item.active .home-banner-content");
            if (activeSlide) {
                activeSlide.classList.add("is-visible");
            }
        };

        bannerCarousel.addEventListener("slide.bs.carousel", () => {
            bannerCarousel.querySelectorAll(".home-banner-content").forEach((content) => {
                content.classList.remove("is-visible");
            });
        });

        bannerCarousel.addEventListener("slid.bs.carousel", activateBannerCaption);
        activateBannerCaption();
    }
})();
