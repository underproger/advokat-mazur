# -*- coding: utf-8 -*-
"""
Переклади для мовних версій.

* Рядки шаблонів обгорнуті в {{ _("…") }}: українська — ключ, переклад — у i18n/<lang>_templates.py (TPL = {ua: ru}).
* Рядки даних (послуги, кейси, відгуки, FAQ) перекладаються за хешем українського тексту:
  i18n/<lang>_data_*.py (DATA = {hash: ru}). Змінили український текст — у звіті з'явиться новий хеш.

Непереклажені рядки збираються у i18n/missing_<lang>.tsv (на сторінці лишається український текст).
"""
import copy
import glob
import hashlib
import importlib.util
import os
import re

HERE = os.path.dirname(os.path.abspath(__file__))
DIR = os.path.join(HERE, "i18n")
CYR = re.compile(r"[А-Яа-яІіЇїЄєҐґ]")
SKIP_KEYS = {"slug", "icon", "hero_img", "note_icon", "id", "service", "initials"}


def h(text):
    return hashlib.md5(text.strip().encode("utf-8")).hexdigest()[:10]


def _load(path, name):
    spec = importlib.util.spec_from_file_location(os.path.basename(path)[:-3], path)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return getattr(mod, name, {})


class Translator:
    def __init__(self, lang):
        self.lang = lang
        self.tpl, self.data = {}, {}
        self.miss_tpl, self.miss_data = {}, {}
        if lang == "uk":
            return
        p = os.path.join(DIR, "%s_templates.py" % lang)
        if os.path.exists(p):
            self.tpl.update(_load(p, "TPL"))
        for p in sorted(glob.glob(os.path.join(DIR, "%s_data_*.py" % lang))):
            self.data.update(_load(p, "DATA"))

    # --- шаблони
    def gettext(self, text):
        if self.lang == "uk" or not text or not CYR.search(text):
            return text
        key = text.strip()
        if key in self.tpl:
            return text.replace(key, self.tpl[key])
        self.miss_tpl[key] = True
        return text

    # --- дані
    def text(self, s):
        if self.lang == "uk" or not CYR.search(s):
            return s
        k = h(s)
        if k in self.data:
            return self.data[k]
        self.miss_data[k] = s
        return s

    def obj(self, x, key=None):
        if key == "initials" and self.lang == "ru" and isinstance(x, str):
            return x.translate(str.maketrans("ІЇЄҐ", "ИИЕГ"))
        if key in SKIP_KEYS:
            return x
        if isinstance(x, str):
            return self.text(x)
        if isinstance(x, dict):
            return {k: self.obj(v, k) for k, v in x.items()}
        if isinstance(x, (list, tuple)):
            return type(x)(self.obj(v) for v in x)
        return copy.deepcopy(x)

    def report(self):
        if self.lang == "uk":
            return 0
        os.makedirs(DIR, exist_ok=True)
        path = os.path.join(DIR, "missing_%s.tsv" % self.lang)
        n = len(self.miss_tpl) + len(self.miss_data)
        with open(path, "w", encoding="utf-8") as f:
            for k in self.miss_tpl:
                f.write("tpl\t%s\n" % k.replace("\n", " "))
            for k, v in self.miss_data.items():
                f.write("data\t%s\t%s\n" % (k, v.replace("\n", " ")))
        if not n and os.path.exists(path):
            os.remove(path)
        return n
