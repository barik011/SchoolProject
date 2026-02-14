(() => {
    const root = document.documentElement;
    const toggleButton = document.getElementById("themeToggle");
    const mainNavbar = document.getElementById("mainNavbar");
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

    if (mainNavbar) {
        const applyNavbarScrolledState = () => {
            if (window.scrollY > 20) {
                mainNavbar.classList.add("navbar-scrolled");
            } else {
                mainNavbar.classList.remove("navbar-scrolled");
            }
        };

        window.addEventListener("scroll", applyNavbarScrolledState, { passive: true });
        applyNavbarScrolledState();
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

    document.querySelectorAll(".dynamic-menu .dropdown-submenu > .dropdown-toggle").forEach((toggle) => {
        toggle.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            const submenu = toggle.nextElementSibling;
            if (!submenu || !submenu.classList.contains("dropdown-menu")) {
                return;
            }

            const siblingMenus = toggle
                .closest(".dropdown-menu")
                ?.querySelectorAll(":scope > .dropdown-submenu > .dropdown-menu.show");

            siblingMenus?.forEach((menu) => {
                if (menu !== submenu) {
                    menu.classList.remove("show");
                }
            });

            submenu.classList.toggle("show");
            toggle.setAttribute("aria-expanded", submenu.classList.contains("show") ? "true" : "false");
        });
    });

    document.querySelectorAll(".dynamic-menu .dropdown").forEach((dropdown) => {
        dropdown.addEventListener("hidden.bs.dropdown", () => {
            dropdown.querySelectorAll(".dropdown-menu.show").forEach((submenu) => {
                submenu.classList.remove("show");
            });
            dropdown.querySelectorAll(".dropdown-toggle[aria-expanded='true']").forEach((toggle) => {
                toggle.setAttribute("aria-expanded", "false");
            });
        });
    });

    const lightbox = document.getElementById("galleryLightbox");
    if (lightbox) {
        const lightboxImage = document.getElementById("galleryLightboxImage");
        const lightboxCaption = document.getElementById("galleryLightboxCaption");
        const closeButton = document.getElementById("galleryLightboxClose");
        const triggers = document.querySelectorAll("[data-gallery-lightbox='true']");

        const openLightbox = (href, title) => {
            if (!lightboxImage || !lightboxCaption) {
                return;
            }
            lightboxImage.src = href;
            lightboxImage.alt = title;
            lightboxCaption.textContent = title;
            lightbox.classList.add("is-open");
            lightbox.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
        };

        const closeLightbox = () => {
            lightbox.classList.remove("is-open");
            lightbox.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener("click", (event) => {
                event.preventDefault();
                const href = trigger.getAttribute("href") || "";
                const title = trigger.getAttribute("data-title") || "";
                openLightbox(href, title);
            });
        });

        closeButton?.addEventListener("click", closeLightbox);
        lightbox.addEventListener("click", (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && lightbox.classList.contains("is-open")) {
                closeLightbox();
            }
        });
    }
})();
