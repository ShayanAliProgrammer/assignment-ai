document.addEventListener("DOMContentLoaded", () => {
  $("body").removeClass("overflow-hidden");
  $("#main-content").removeClass("hidden");
  $("#screen-loader").remove();

  let questions = [];

  function renderQuestions() {
    $("#question-list").empty();
    questions.forEach((q, i) => {
      $("#question-list").append(`
        <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
          ${$("<div>").text(q).html()}
          <button class="cursor-pointer size-max focus:!ring-red-700 delete-btn ml-auto p-2 rounded-lg bg-red-700 text-white text-red-500" data-index="${i}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </li>`);
    });
  }

  $("#add-form").on("submit", (e) => {
    e.preventDefault();
    const q = $("#new-question").val().trim();
    if (!q) return;
    questions.push(q);
    $("#new-question").val("");
    renderQuestions();
  });

  $("#new-question").on("paste", (e) => {
    e.preventDefault();
    const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
    const pastedText = clipboardData.getData("text");
    if (!pastedText) return;
    const newQuestions = pastedText
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line.length > 0);
    if (newQuestions.length === 0) return;
    questions = questions.concat(newQuestions);
    $("#new-question").val("");
    renderQuestions();
  });

  $(document).on("click", ".delete-btn", function () {
    const index = $(this).data("index");
    if (confirm("Are you sure?")) {
      questions.splice(index, 1);
      renderQuestions();
    }
  });

  $("#generate-form").on("submit", (e) => {
    e.preventDefault();
    if (!questions.length) return alert("Add some questions first!");

    const minWords = parseInt($("#min-words").val(), 10);
    const maxWords = parseInt($("#max-words").val(), 10);

    if (isNaN(minWords) || isNaN(maxWords))
      return alert("Enter both min and max word counts!");
    if (minWords >= maxWords)
      return alert("Minimum words must be less than maximum words!");

    $("#progress-list").empty().removeClass("hidden");
    $("#question-list").empty().addClass("hidden");

    questions.forEach((q, i) => {
      $("#progress-list").append(`
        <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="loader size-6 shrink-0 animate-spin hidden"><path d="M10.1 2.18a9.93 9.93 0 0 1 3.8 0"/><path d="M17.6 3.71a9.95 9.95 0 0 1 2.69 2.7"/><path d="M21.82 10.1a9.93 9.93 0 0 1 0 3.8"/><path d="M20.29 17.6a9.95 9.95 0 0 1-2.7 2.69"/><path d="M13.9 21.82a9.94 9.94 0 0 1-3.8 0"/><path d="M6.4 20.29a9.95 9.95 0 0 1-2.69-2.7"/><path d="M2.18 13.9a9.93 9.93 0 0 1 0-3.8"/><path d="M3.71 6.4a9.95 9.95 0 0 1 2.7-2.69"/><circle cx="12" cy="12" r="1"/></svg>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="checkmark size-6 shrink-0 hidden"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          ${$("<div>").text(q).html()}
        </li>`);
    });

    const query = new URLSearchParams({
      questions: JSON.stringify(questions),
      min_words: minWords,
      max_words: maxWords,
    }).toString();

    const evt = new EventSource(`/assignment/progress?${query}`);

    evt.addEventListener("start", (e) => {
      const i = parseInt(e.data);
      $(`#q-${i} .dot`).addClass("hidden");
      $(`#q-${i} .checkmark`).addClass("hidden");
      $(`#q-${i} .loader`).removeClass("hidden");
    });

    evt.addEventListener("done", (e) => {
      const i = parseInt(e.data);
      $(`#q-${i} .dot`).addClass("hidden");
      $(`#q-${i} .loader`).addClass("hidden");
      $(`#q-${i} .checkmark`).removeClass("hidden");
    });

    evt.addEventListener("complete", (e) => {
      evt.close();
      const data = JSON.parse(e.data);
      const markdown = data?.markdown;

      const form = $("<form>", {
        method: "POST",
        action: "/assignment/docx",
        style: "display: none;",
      });

      form.append(
        $("<input>", {
          type: "hidden",
          name: "markdown",
          value: markdown,
        })
      );

      $("body").append(form);
      form.trigger("submit");
    });
  });
});
