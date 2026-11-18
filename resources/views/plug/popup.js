(() => {
  const PRESETS = window.G1_ZERO_COST_PRESETS || [];
  const ALIAS = window.G1_PK_ALIAS || {};
  const statusEl = document.getElementById("popup-status");
  const usBtn = document.getElementById("popup-us-btn");
  const pkLink = document.getElementById("popup-pk");
  let skuKey = "20tb";
  let months = 12;

  function setPopupStatus(text, isOk = false, isErr = false) {
    statusEl.textContent = text;
    statusEl.title = text;
    statusEl.style.background = isErr ? "#fdecec" : isOk ? "#e8f8ef" : "#eef8ff";
    statusEl.style.borderColor = isErr ? "#f5b5b5" : isOk ? "#9ad7b5" : "#c7e7f7";
    statusEl.style.color = isErr ? "#b42318" : isOk ? "#157347" : "#176a8f";
  }

  function findPreset(id) {
    const real = ALIAS[id] || id;
    return PRESETS.find((p) => p.id === real) || null;
  }

  function currentPreset() {
    return PRESETS.find((p) => p.skuKey === skuKey && p.months === months) || PRESETS[0];
  }

  function paint() {
    document.querySelectorAll("#sku-row .chip").forEach((b) => b.classList.toggle("is-on", b.dataset.sku === skuKey));
    document.querySelectorAll("#period-row .chip").forEach((b) => b.classList.toggle("is-on", Number(b.dataset.months) === months));
  }

  function selectCurrent() {
    const p = currentPreset();
    if (!p) return;
    chrome.storage.local.set({ g1ZcSelected: p.id });
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
      const tab = tabs && tabs[0];
      if (tab && tab.id && (tab.url || "").includes("one.google.com")) {
        chrome.tabs.sendMessage(tab.id, { action: "SELECT_PRESET", presetId: p.id }, () => {
          void chrome.runtime.lastError;
          setPopupStatus("已挂载 " + p.title, true);
        });
      } else {
        setPopupStatus("已记住 " + p.title, true);
      }
    });
    paint();
  }

  document.querySelectorAll("#sku-row .chip").forEach((b) => {
    b.addEventListener("click", () => {
      skuKey = b.dataset.sku;
      selectCurrent();
    });
  });
  document.querySelectorAll("#period-row .chip").forEach((b) => {
    b.addEventListener("click", () => {
      months = Number(b.dataset.months);
      selectCurrent();
    });
  });

  if (pkLink) {
    pkLink.addEventListener("click", (e) => {
      e.preventDefault();
      chrome.tabs.create({ url: "https://t.me/pk_oa" });
    });
  }

  chrome.storage.local.get(["g1ZcSelected"], (res) => {
    const saved = findPreset(res && res.g1ZcSelected);
    if (saved) {
      skuKey = saved.skuKey;
      months = saved.months;
    }
    paint();
  });

  usBtn.addEventListener("click", () => {
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
      const tab = tabs && tabs[0];
      if (tab && tab.id && (tab.url || "").includes("payments.google.com")) {
        chrome.tabs.sendMessage(tab.id, { action: "FILL_US_ADDRESS" });
        setPopupStatus("已填 OR 97201", true);
      } else {
        chrome.tabs.create({ url: "https://payments.google.com/gp/w/home/settings" });
        window.close();
      }
    });
  });
})();
