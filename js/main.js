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


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 0) {
            $('.navbar').addClass('position-fixed fixesnavbar  shadow-sm');
        } else {
            $('.navbar').removeClass('position-fixed fixesnavbar shadow-sm');
        }
    });


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
  const query = searchInput.value.trim().toLowerCase();
  let count = 0;

  cards.forEach(card => {
    const category = card.dataset.category;
    const text = card.textContent.toLowerCase();

    const categoryMatch =
      activeCategory === "all" || category === activeCategory;

    const searchMatch = text.includes(query);
    const show = categoryMatch && searchMatch;

    card.classList.toggle("d-none", !show);

    if (show) count++;
  });

  noResults.hidden = count > 0;
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

searchInput.addEventListener("input", filterSchemes);
searchBtn.addEventListener("click", filterSchemes);


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
  const result = document.getElementById("eligibilityResult");

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const age = Number(document.getElementById("citizenAge").value);
    const state = document.getElementById("citizenState").value;
    const income = document.getElementById("familyIncome").value;
    const category = document.getElementById("citizenCategory").value;

    result.hidden = false;
    result.className = "eligibility-result";

    if (
      !Number.isFinite(age) ||
      age < 1 || age > 120 ||
      !state || !income || !category
    ) {
      result.classList.add("is-error");
      result.innerHTML = `
        <h4><i class="fa-solid fa-circle-exclamation"></i>
        Please check your details</h4>
        <p>Enter a valid age and complete all required fields.</p>
      `;
      return;
    }

    // Demo only: these are not official scheme eligibility rules.
    result.classList.add("is-warning");
    result.innerHTML = `
      <h4><i class="fa-solid fa-circle-info"></i>
      Your Details Are Ready!</h4>
      <p>
        You entered age <strong>${age}</strong>,
        state <strong>${state}</strong>,
        annual income <strong>${income === "1000000"
          ? "Above ₹8 lakh"
          : Number(income) === 100000
            ? "Below ₹1 lakh"
            : Number(income) === 300000
              ? "₹1 lakh – ₹3 lakh"
              : Number(income) === 500000
                ? "₹3 lakh – ₹5 lakh"
                : "₹5 lakh – ₹8 lakh"}</strong>,
        and category <strong>${category}</strong>.
      </p>
      <p style="margin-top:10px">
        <strong>Next step:</strong> Match these details against the
        official eligibility criteria of each scheme. This demo
        does not determine whether you qualify for any scheme.
      </p>
    `;

    result.scrollIntoView({
      behavior: "smooth",
      block: "nearest"
    });
  });
});
