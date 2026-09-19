const STATUS = {
  public: {
    next: "publicoff",
    icon: "public.svg",
    label: "公開",
    labelEn: "public",
  },
  publicoff: {
    next: "pending",
    icon: "publicoff.svg",
    label: "非公開",
    labelEn: "publicoff",
  },
  pending: {
    next: "public",
    icon: "pending.svg",
    label: "回答待ち",
    labelEn: "pending",
  },
};

const DATA_STATUS = {
  order: {
    desc: { next: "asc", value: "desc" },
    asc: { next: "none", value: "asc" },
    none: { next: "desc", value: "none" },
  },
  exist: {
    on: { next: "none", value: "on" },
    none: { next: "on", value: "none" },
  },
  def: { next: "none" },
};

let cards = [];
function toggleShow(card, question) {
  const show = !card.hasAttribute("show");
  card.toggleAttribute("show", show);
  question.setAttribute("aria-expanded", show);
}
function extendAll(extend = true) {
  cards.forEach((ele) => {
    if (extend) {
      ele.setAttribute("show", "");
    } else {
      ele.removeAttribute("show");
    }
  });
}
document.querySelectorAll(".question").forEach((ele) => {
  const card = ele.closest(".card");
  cards.push(card);

  ele.addEventListener("click", () => toggleShow(card, ele));
  ele.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      toggleShow(card, ele);
    }
  });

  const cardStatus = ele.querySelector(".card-status");
  if (cardStatus) {
    const status = STATUS[cardStatus.dataset.status] ?? STATUS.publicoff;
    cardStatus.getElementsByTagName("p")[0].innerText = status.labelEn;
    cardStatus.getElementsByTagName("img")[0].src = `/src/img/${status.icon}`;
  }
});

document.querySelectorAll(".card").forEach((card) => {
  const submitBtn = card.querySelector(".dashboard-submit-button");
  if (!submitBtn) return;
  const activate = () => (submitBtn.disabled = false);
  card.addEventListener("change", activate);
  card.addEventListener("input", activate);
});

const sortButtons = Array.from(
  document.querySelectorAll(".list-sort-list>button"),
);
const searchInput = document.querySelector(".list-search input");
const cardList = document.querySelector(".box-card-list");

/** 検索語・絞り込み・並び替えをカード一覧に適用する（クライアント側） */
function refreshList() {
  if (!cardList) return;
  const state = {};
  sortButtons.forEach((b) => (state[b.dataset.key] = b.dataset.sort));
  const q = (searchInput?.value ?? "").trim().toLowerCase();
  // 回答待ち(pending)・公開・非公開は状態での絞り込み。複数オンなら和集合
  const statusFilter = [
    ["waiting", "pending"],
    ["public", "public"],
    ["publicoff", "publicoff"],
  ]
    .filter(([key]) => state[key] === "on")
    .map(([, status]) => status);
  const num = (card, key) => Number(card.dataset[key] ?? 0);

  const items = Array.from(cardList.querySelectorAll(":scope > .card"));
  items.forEach((card) => {
    let show = !q || (card.dataset.search ?? "").includes(q);
    if (statusFilter.length && !statusFilter.includes(card.dataset.status)) {
      show = false;
    }
    card.hidden = !show;
  });

  // 並び替え: 回答順が有効なら回答日時、それ以外は投稿日時（デフォルトは新しい順）
  const key = ["asc", "desc"].includes(state.answered) ? "answered" : "posted";
  const dir = state[key] === "asc" ? 1 : -1;
  const field = key === "answered" ? "answered" : "posted";
  items.sort((x, y) => {
    if (field === "answered") {
      // 未回答は常に末尾
      if (!num(x, field) || !num(y, field)) {
        return (num(x, field) ? 0 : 1) - (num(y, field) ? 0 : 1);
      }
    }
    return dir * (num(x, field) - num(y, field));
  });
  items.forEach((card) => cardList.appendChild(card));
}

sortButtons.forEach((ele) => {
  const apply = (status) => {
    ele.setAttribute("data-sort", status);
  };
  ele.addEventListener("click", () => {
    const sort = ele.getAttribute("data-sort");
    const type = ele.getAttribute("data-type");
    const status = DATA_STATUS[type][sort] ?? DATA_STATUS.def;
    apply(status.next);
    // 投稿順・回答順は同時に有効にしない
    if (type === "order" && status.next !== "none") {
      sortButtons.forEach((other) => {
        if (other !== ele && other.dataset.type === "order") {
          other.setAttribute("data-sort", "none");
        }
      });
    }
    refreshList();
  });
  const sort = ele.getAttribute("data-sort");
  const type = ele.getAttribute("data-type");
  apply(DATA_STATUS[type][sort].value);
});
searchInput?.addEventListener("input", refreshList);
refreshList();

document.querySelectorAll(".form-group").forEach((g) => {
  const field = g.querySelector("input, textarea");
  const count = g.querySelector(".form-char-count");
  if (!field || !count) return;
  const isRequired = field.hasAttribute("data-required");
  const maxLength = parseInt(field.dataset.maxLength);
  const minLength = parseInt(field.dataset.minLength);
  const update = () => {
    if (!maxLength && !minLength) {
      count.textContent = "";
      return;
    }
    const length = field.value.length;
    count.textContent = maxLength ? `${length}/${maxLength}` : `${length}`;
    const invalid =
      (maxLength && length > maxLength) || (minLength && length < minLength);
    count.classList.toggle("warn-text", !!invalid && (isRequired || length !== 0));
  };
  field.addEventListener("input", update);
  update();
});

document.querySelectorAll(".status-change-button").forEach((ele) => {
  const image = ele.querySelector("img");
  const text = ele.querySelector("span");
  const input = ele.parentElement.querySelector("input");

  const apply = (status, notify = false) => {
    if (!STATUS[status]) status = "publicoff";
    ele.dataset.status = status;
    image.src = `/src/img/${STATUS[status].icon}`;
    text.textContent = STATUS[status].label;
    input.value = STATUS[status].labelEn;
    if (notify) {
      input.dispatchEvent(new Event("change", { bubbles: true }));
    }
  };

  ele.setStatus = (status) => apply(status); // 外部から状態だけ更新（発火させない）
  apply(ele.dataset.status); // 初期化：発火させない
  ele.addEventListener(
    "click",
    () => apply(STATUS[ele.dataset.status].next, true), // クリック：発火させる
  );
});

const popup = document.getElementById("popup");

function showPopup(type, title, message, actionMessage, cancelMessage = null) {
  popup.querySelector(".popup-title").innerText = title;

  const submit = popup.querySelector(".popup-submit");
  submit.classList.remove("main-button-danger", "main-button-primary");
  submit.classList.add(
    type === "error" ? "main-button-danger" : "main-button-primary",
  );
  submit.innerText = actionMessage;

  const sub = popup.querySelector(".popup-sub");
  sub.style.display = cancelMessage ? "" : "none";
  if (cancelMessage) sub.innerText = cancelMessage;

  popup.querySelector(".popup-message").innerText = message;

  return new Promise((resolve) => {
    popup.addEventListener(
      "close",
      () => {
        resolve(popup.returnValue === "ok");
      },
      {
        once: true,
      },
    );
    popup.showModal();
  });
}

popup.addEventListener("click", (e) => {
  if (e.target === popup) popup.close("cancel");
});

const PASSWORD_INPUT_STATUS = {
  text: { type: "text", icon: "visibility.svg", next: "password" },
  password: { type: "password", icon: "visibilityoff.svg", next: "text" },
};

document.querySelectorAll(".password-input").forEach((ele) => {
  const image = ele.querySelector("img");
  const input = ele.querySelector("input");
  const passwordStatusChange = (status) => {
    const nextStatus = PASSWORD_INPUT_STATUS[status];
    input.setAttribute("type", nextStatus.type);
    image.src = `/src/img/${nextStatus.icon}`;
  };
  image.addEventListener("click", () => {
    const currentStatus =
      PASSWORD_INPUT_STATUS[input.getAttribute("type")] ??
      PASSWORD_INPUT_STATUS.password;
    passwordStatusChange(currentStatus.next);
  });
});

const createQuestionBtn = document.getElementsByClassName(
  "main-create-question-button",
)[0];
const subCreateQuestionBtn = document.getElementsByClassName(
  "circle-create-question-button",
)[0];
const boxView = document.getElementsByClassName("box-view")[0];
if (createQuestionBtn && subCreateQuestionBtn) {
  const observer = new IntersectionObserver(
    ([entry]) => {
      // 質問ボタンが画面外 → 丸ボタンを表示
      subCreateQuestionBtn.toggleAttribute("show", !entry.isIntersecting);
    },
    {
      root: null, // スクロールするのが .box-view なら root: boxView に
      threshold: 0,
    },
  );
  observer.observe(createQuestionBtn);
}
