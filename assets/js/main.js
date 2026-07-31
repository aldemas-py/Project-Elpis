/**
 * Elpis Counselling Centre - Main JavaScript
 */

document.addEventListener("DOMContentLoaded", function () {
  // =========================================================
  // Mobile Navigation Toggle
  // =========================================================
  const navToggle = document.querySelector(".nav-toggle");
  const navLinks = document.querySelector(".nav-links");

  if (navToggle) {
    navToggle.addEventListener("click", function () {
      navLinks.classList.toggle("open");
      // Animate hamburger to X
      const spans = this.querySelectorAll("span");
      spans.forEach((span) => span.classList.toggle("active"));
    });
  }

  // Close mobile nav when a link is clicked
  if (navLinks) {
    navLinks.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", function () {
        navLinks.classList.remove("open");
      });
    });
  }

  // =========================================================
  // Navbar Background on Scroll
  // =========================================================
  const navbar = document.querySelector(".navbar");
  let lastScroll = 0;

  window.addEventListener("scroll", function () {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      navbar.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.15)";
    } else {
      navbar.style.boxShadow = "none";
    }

    lastScroll = currentScroll;
  });

  // =========================================================
  // Smooth Scroll for Anchor Links
  // =========================================================
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href === "#") return;

      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        const headerOffset = 80;
        const elementPosition = target.getBoundingClientRect().top;
        const offsetPosition =
          elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth",
        });
      }
    });
  });

  // =========================================================
  // Testimonials Carousel (if more than 3)
  // =========================================================
  const testimonialsGrid = document.querySelector(".testimonials-grid");
  if (testimonialsGrid && testimonialsGrid.children.length > 3) {
    // Simple auto-scroll functionality
    let isDown = false;
    let startX;
    let scrollLeft;

    testimonialsGrid.addEventListener("mousedown", (e) => {
      isDown = true;
      testimonialsGrid.style.cursor = "grabbing";
      startX = e.pageX - testimonialsGrid.offsetLeft;
      scrollLeft = testimonialsGrid.scrollLeft;
    });

    testimonialsGrid.addEventListener("mouseleave", () => {
      isDown = false;
      testimonialsGrid.style.cursor = "grab";
    });

    testimonialsGrid.addEventListener("mouseup", () => {
      isDown = false;
      testimonialsGrid.style.cursor = "grab";
    });

    testimonialsGrid.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - testimonialsGrid.offsetLeft;
      const walk = (x - startX) * 2;
      testimonialsGrid.scrollLeft = scrollLeft - walk;
    });
  }

  // =========================================================
  // Form Validation
  // =========================================================
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      let isValid = true;
      const requiredFields = this.querySelectorAll("[required]");

      requiredFields.forEach((field) => {
        const errorEl = field.parentElement.querySelector(".error-message");
        if (errorEl) errorEl.remove();

        if (!field.value.trim()) {
          isValid = false;
          showFieldError(field, "This field is required");
        } else if (field.type === "email" && !isValidEmail(field.value)) {
          isValid = false;
          showFieldError(field, "Please enter a valid email address");
        } else if (field.type === "tel" && !isValidPhone(field.value)) {
          isValid = false;
          showFieldError(field, "Please enter a valid phone number");
        }
      });

      if (!isValid) {
        e.preventDefault();
      }
    });
  });

  function showFieldError(field, message) {
    field.style.borderColor = "#dc3545";
    const error = document.createElement("small");
    error.className = "error-message";
    error.style.cssText =
      "color: #dc3545; font-size: 0.8rem; margin-top: 0.3rem; display: block;";
    error.textContent = message;
    field.parentElement.appendChild(error);
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidPhone(phone) {
    return /^[\d\s\+\-\(\)]{7,15}$/.test(phone);
  }

  // Clear validation styling on input
  document.querySelectorAll(".form-control").forEach((field) => {
    field.addEventListener("input", function () {
      this.style.borderColor = "";
      const error = this.parentElement.querySelector(".error-message");
      if (error) error.remove();
    });
  });

  // =========================================================
  // Animate Stats Counter (Admin Dashboard)
  // =========================================================
  const statValues = document.querySelectorAll(".stat-value");
  if (statValues.length > 0) {
    statValues.forEach((stat) => {
      const target = parseInt(stat.textContent.replace(/,/g, ""));
      if (!isNaN(target) && target > 0) {
        animateCounter(stat, target);
      }
    });
  }

  function animateCounter(element, target) {
    let current = 0;
    const increment = Math.ceil(target / 50);
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      element.textContent = current.toLocaleString();
    }, 30);
  }

  // =========================================================
  // Back to Top Button (optional)
  // =========================================================
  const backToTop = document.createElement("button");
  backToTop.className = "back-to-top";
  backToTop.innerHTML = "&#8593;";
  backToTop.style.cssText = `
        position: fixed; bottom: 2rem; right: 2rem; z-index: 999;
        width: 45px; height: 45px; border-radius: 50%;
        background: #4FA08A; color: #fff; border: none;
        font-size: 1.2rem; cursor: pointer; opacity: 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 15px rgba(79, 160, 138, 0.3);
        display: none;
    `;
  document.body.appendChild(backToTop);

  window.addEventListener("scroll", function () {
    if (window.pageYOffset > 400) {
      backToTop.style.display = "block";
      setTimeout(() => {
        backToTop.style.opacity = "1";
      }, 10);
    } else {
      backToTop.style.opacity = "0";
      setTimeout(() => {
        backToTop.style.display = "none";
      }, 300);
    }
  });

  backToTop.addEventListener("click", function () {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
});
