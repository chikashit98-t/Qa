const MESSAGES = {
  boxTitle: {
    required: "* タイトルを入力してください",
    maxLength: "* タイトルは20文字以内で入力してください",
    minLength: "* タイトルは8文字以上で入力してください",
  },
  content: {
    required: "* 内容を入力してください",
    maxLength: "* 内容は200文字以内で入力してください",
  },
  boxID: {
    minLength: "* 10文字以上",
    maxLength: "* 50文字以下",
    invalidChar: "* 半角英数字と記号「-_!*'()」のみ使用できます。",
    required: "* IDを入力してください",
    taken: "* このIDは既に使われています",
  },
  password: {
    required: "* パスワードを入力してください",
    minLength: "* パスワードは8文字以上で入力してください",
    invalidChar: "* パスワードに使用できない文字が含まれています",
    weak: "* 英字と数字をそれぞれ1文字以上含めてください",
    invalid: "質問箱IDかパスワードが違います",
    locked: "試行回数が上限に達しました。{0}分ほど待ってからやり直してください",
  },
  confirm: {
    required: "* 確認用パスワードを入力してください",
    notSame: "* パスワードが一致しません",
  },
  email: {
    required: "* メールアドレスを入力してください",
    invalidChar: "* メールアドレスの形式が正しくありません",
  },
  loginId: {
    required: "* ログインIDを入力してください",
  },
  confirmCode: {
    required: "* 確認コードを入力してください",
    length: "* 確認コードは8文字です",
    invalidChar: "* 確認コードは半角英数字です",
    mismatch: "* 確認コードが違います",
    taken: "* このIDは既に使われました。最初からやり直してください",
  },
  token: {
    invalid: "このURLは無効か、有効期限が切れています。最初からやり直してください。",
  },
  auth: {
    required: "ログインが必要です。",
  },
  question: {
    notFound: "質問が見つかりませんでした。",
  },
  server: {
    error: "サーバーでエラーが発生しました。時間をおいて再度お試しください。",
  },
  status: {
    invalid: "* 状態が正しくありません",
  },
  answerTitle: {
    required: "* 回答のタイトルを入力してください",
    maxLength: "* タイトルは50文字以内で入力してください",
  },
  answerContent: {
    required: "* 回答の内容を入力してください",
    maxLength: "* 内容は200文字以内で入力してください",
  },
  questionTitle: {
    required: "* 質問のタイトルを入力してください",
    maxLength: "* タイトルは50文字以内で入力してください",
  },
};

const PATTERN = {
  boxID: { regex: /^[A-Za-z0-9\-_!*'()]+$/ },
  password: { regex: /^[\x21-\x7e]+$/ },
  email: { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ },
  confirmCode: { regex: /^[A-Za-z0-9]+$/ },
};

function runRule(el, form) {
  const value = el.value ?? "";
  const ns = el.name;
  const found = [];

  const required = el.hasAttribute("data-required");

  if (value === "" || (required && value.trim() === "")) {
    if (required) {
      found.push({ code: `${ns}.required` });
    }
    return found;
  }

  const minLength = el.dataset.minLength;
  if (minLength && value.length < Number(minLength)) {
    found.push({ code: `${ns}.minLength` });
  }

  const maxLength = el.dataset.maxLength;
  if (maxLength && value.length > Number(maxLength)) {
    found.push({ code: `${ns}.maxLength` });
  }
  const charLength = el.dataset.charLength;
  if (charLength && value.length !== Number(charLength)) {
    found.push({ code: `${ns}.length` });
  }

  const invalidChar = el.hasAttribute("data-invalid-char");
  if (invalidChar && ns && !PATTERN[ns].regex.test(value)) {
    found.push({ code: `${ns}.invalidChar` });
  }
  switch (ns) {
    case "password":
      if (!/[A-Za-z]/.test(value) || !/\d/.test(value)) {
        found.push({ code: `${ns}.weak` });
      }
      break;
    case "confirm":
      const pass = form.querySelector('*[name="password"]');
      if (pass && value !== pass.value) {
        found.push({ code: `${ns}.notSame` });
      }
      break;
  }
  return found;
}

function validate(form) {
  const errors = {};
  const validateEls = form.querySelectorAll(
    "input[data-validate-on] ,textarea[data-validate-on], button[data-validate-on]",
  );

  validateEls.forEach((el) => {
    const found = runRule(el, form);
    if (found.length) errors[el.name] = found;
  });

  return errors;
}

function toMessage(error) {
  const path = String(error.code).split(".");
  const message = MESSAGES[path[0]]?.[path[1]] ?? String(error.code);
  if (!error["arg"]) return message;

  const arg = Array.from(error.arg);
  return arg.reduce((m, a, i) => m.replaceAll(`{${i}}`, a), message);
}

function createError(errorBox, error) {
  let messageSpan = errorBox.querySelector(`span[data-code="${error.code}"]`);
  if (!messageSpan) {
    messageSpan = document.createElement("span");
    const message = toMessage(error);
    messageSpan.innerText = message;
    messageSpan.dataset.code = error.code;
    errorBox.append(messageSpan);
  }
  messageSpan.dataset.red = true;
}

function showErrors(form, errors) {
  const validateEls = form.querySelectorAll(
    "input[data-validate-on] ,textarea[data-validate-on], button[data-validate-on]",
  );
  validateEls.forEach((ele) => {
    const ns = ele.name;
    const errorList = errors[ns] || [];

    const formGroupNode = ele.closest(".form-group");
    const errorMessageBox = formGroupNode.querySelector(".form-error-message");
    errorList.forEach((error) => {
      form.dataset.error = "true";
      createError(errorMessageBox, error);
    });
  });
}

async function send(form, submitter) {
  const formData = new FormData(form, submitter);
  const method = (form.method || "get").toUpperCase();

  let url = form.action;
  const init = { method };

  if (method === "GET" || method === "HEAD") {
    const query = new URLSearchParams(formData).toString();
    if (query) {
      url += (url.includes("?") ? "&" : "?") + query;
    }
  } else {
    init.headers = { "Content-Type": "application/json" };
    init.body = JSON.stringify(Object.fromEntries(formData.entries()));
  }

  const response = await fetch(url, init);
  if (response.redirected) {
    return { redirected: true, url: response.url };
  }
  let data = {};
  try {
    data = await response.json();
  } catch (e) {
    data = { errors: { _: [{ code: "server.error" }] } };
  }
  return { redirected: false, status: response.status, data };
}

function statusOK(status) {
  return 200 <= status && status <= 299;
}

document.querySelectorAll("form[data-validate]").forEach((form) => {
  const groups = Array.from(form.getElementsByClassName("form-error-message"));
  const clearErrors = () => {
    form.dataset.error = "false";
    groups.forEach((box) => {
      Array.from(box.children).forEach((child) => {
        if (child.hasAttribute("data-validate-nodelete")) {
          child.dataset.red = false;
        } else {
          child.remove();
        }
      });
    });
  };

  let submitted = false;
  const only = form.dataset.onlyValidate;
  const method = (form.method || "get").toUpperCase();
  form.addEventListener("submit", async (event) => {
    const submitter = event.submitter;

    if (submitter && only && submitter.value !== only) return;

    clearErrors();
    submitted = true;
    const error = validate(form);
    showErrors(form, error);
    if (Object.keys(error).length > 0) {
      event.preventDefault();
      return;
    }

    if (method === "GET" || method === "HEAD") {
      // 通常のページ遷移に任せる（fetchでJSONとして扱わない）
      return;
    }

    event.preventDefault();
    if (form.dataset.sending === "true") return;
    form.dataset.sending = "true";
    try {
      const result = await send(form, submitter);
      if (result.redirected) {
        location.href = result.url;
        return;
      }
      const ok = statusOK(result.status);
      if (!ok) {
        const errors = result.data.errors ?? {};
        const { _: general, ...fieldErrors } = errors;
        if (general?.length) {
          // フォーム項目に紐づかないエラー（ログイン切れ・無効なトークン等）
          await showPopup("error", "エラー", toMessage(general[0]), "閉じる");
          if (result.status === 401) location.href = "/login";
        }
        clearErrors();
        showErrors(form, fieldErrors);
        return;
      }
      if (result.data.type === "setting") {
        await showPopup("message", "保存しました", "設定を保存しました。", "閉じる");
        form.closest("dialog")?.close("save");
        location.reload();
      } else if (result.data.type === "answer") {
        await showPopup("message", "保存しました", "回答を保存しました。", "閉じる");
        if (result.data.status) {
          // 自動公開など、サーバーで確定した状態を画面に反映する
          form.querySelector(".status-change-button")?.setStatus(result.data.status);
          form.dataset.status = result.data.status;
        }
        const saveBtn = form.querySelector(".dashboard-submit-button");
        if (saveBtn) saveBtn.disabled = true;
        form.dispatchEvent(new CustomEvent("answer-saved"));
      }
    } catch (e) {
      await showPopup("error", "エラー", e.message, "閉じる");
    } finally {
      form.dataset.sending = "false";
    }
  });
  form.addEventListener("input", () => {
    if (submitted) {
      clearErrors();
      const error = validate(form);
      showErrors(form, error);
    }
  });
});
