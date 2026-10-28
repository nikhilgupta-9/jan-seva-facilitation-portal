(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();


    // Initiate the wowjs
    new WOW().init();


    // Sticky navbar is handled by CSS (position: sticky) - see .navbar
    // in css/style.css. The old scroll-triggered position-fixed toggle
    // left the navbar with no top offset, so on scroll it pinned at
    // whatever y-coordinate it happened to be at instead of y=0 -
    // showing a gap above it and clipping on mobile when the browser
    // chrome collapsed.


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow').css('display', 'flex');
        } else {
            $('.back-to-top').fadeOut('slow').css('display', 'none');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({
            scrollTop: 0
        }, 1500, 'easeInOutExpo');
        return false;
    });

})(jQuery);




const searchInput = document.getElementById("schemeSearch");
const searchBtn = document.getElementById("searchBtn");
const cards = document.querySelectorAll(".scheme-item");
const filterButtons = document.querySelectorAll(".filter-btn");
const noResults = document.getElementById("noResults");

let activeCategory = "all";

function filterSchemes() {
  const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
  let count = 0;

  cards.forEach(card => {
    const category = card.dataset.category;
    const text = card.textContent.toLowerCase();

    const categoryMatch =
      activeCategory === "all" || category === activeCategory;

    const searchMatch = !query || text.includes(query);
    const show = categoryMatch && searchMatch;

    card.classList.toggle("d-none", !show);

    if (show) count++;
  });

  if (noResults) {
    noResults.hidden = count > 0;
  }
}

filterButtons.forEach(button => {
  button.addEventListener("click", () => {
    activeCategory = button.dataset.filter;

    filterButtons.forEach(btn => {
      btn.classList.remove("btn-primary");
      btn.classList.add("btn-outline-primary");
    });

    button.classList.add("btn-primary");
    button.classList.remove("btn-outline-primary");

    filterSchemes();
  });
});

if (searchInput) {
  searchInput.addEventListener("input", filterSchemes);
}
if (searchBtn) {
  searchBtn.addEventListener("click", filterSchemes);
}


document.querySelectorAll(".process-card").forEach(function(card) {
  card.setAttribute("tabindex", "0");
});



document.addEventListener('DOMContentLoaded', function () {

    const accordion = document.getElementById('govFaqAccordion');

    if (!accordion) return;

    accordion.addEventListener('click', function (event) {

        const question = event.target.closest('.faq-question');

        if (!question || !accordion.contains(question)) return;

        const currentItem = question.closest('.faq-item');
        const currentAnswer = currentItem.querySelector('.faq-answer');
        const isOpen = currentItem.classList.contains('active');

        // Close all FAQ items
        accordion.querySelectorAll('.faq-item').forEach(function (item) {

            item.classList.remove('active-faq');

            const answer = item.querySelector('.faq-answer');
            const btn = item.querySelector('.faq-question');
            const icon = item.querySelector('.faq-icon');

            answer.style.display = 'none';
            btn.setAttribute('aria-expanded', 'false');

            if (icon) {
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
        });

        // Open clicked item
        if (!isOpen) {

            currentItem.classList.add('active-faq');
            currentAnswer.style.display = 'block';

            question.setAttribute('aria-expanded', 'true');

            const currentIcon = question.querySelector('.faq-icon');

            if (currentIcon) {
                currentIcon.classList.remove('fa-plus');
                currentIcon.classList.add('fa-minus');
            }
        }

    });

});



document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("eligibilityForm");
  if (form && typeof window.handleEligibilitySubmit === "function") {
    form.addEventListener("submit", window.handleEligibilitySubmit);
  }
});
