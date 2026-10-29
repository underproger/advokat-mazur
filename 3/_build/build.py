# -*- coding: utf-8 -*-
"""
Збірка статичних сторінок дизайну 3.

    python3 3/_build/build.py

Шаблони: _build/templates/*.j2, контент послуг: _build/data_services.py.
Результат: 3/index.html, 3/<slug>.html, 3/keysy.html, 3/keys-<slug>.html + те саме в 3/ru/.
Сторінки згенеровані — правки контенту вносимо в дані/шаблони і перезбираємо.
"""
import os
import sys

from jinja2 import Environment, FileSystemLoader, StrictUndefined

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.dirname(HERE)
sys.path.insert(0, HERE)
from data_services import SERVICES, DEFAULT_STEPS_HEAD, SHARED_FAQ_FIRST, rv, faq  # noqa: E402
from data_cases import CASES  # noqa: E402
from i18n import Translator  # noqa: E402

SITE = {
    # контакти — з чинного сайту advokatmazur.com.ua
    "name": "Сергій Мазур",
    "cert": "4073",
    "years": "20",
    "phone": "+38 096 912 48 00",
    "phone_href": "+380969124800",
    "email": "advocatemazur@gmail.com",
    "address": "м. Київ, вул. Симона Петлюри, 8",
    "hours": "Пн–Пт 9:00–19:00",
    "telegram": "https://t.me/+380969124800",
    "viber": "viber://chat?number=%2B380969124800",
    "whatsapp": "https://wa.me/380969124800",
}

# Відгуки та FAQ головної — з дизайну головної, з перелінковкою на послуги
HOME_REVIEWS = [
    rv("ОК", "Олександр К.", "Кримінальна справа", "2 тижні тому", "Адвокат одразу пояснив, що робити далі, і був поруч на кожному етапі. Результатом задоволений."),
    rv("ІМ", "Ірина М.", "ТЦК та СП", "1 місяць тому", "Допоміг оскаржити рішення ВЛК. Усе пояснював простими словами і завжди був на зв’язку."),
    rv("АТ", "Андрій, ТОВ «Транслайн»", "Ліцензія на перевезення", "1 місяць тому", "Оформили ліцензію на вантажні перевезення без жодної відмови. Окремо дякую за підготовку до перевірки."),
    rv("ВР", "Віталій Р.", "Код 95", "2 місяці тому", "Швидко зорієнтував щодо Коду 95 і чіп-карти. Зекономив мені тиждень біганини по інстанціях."),
    rv("НС", "Наталія С.", "Економічні злочини", "3 місяці тому", "Складна справа щодо податків. Адвокат Мазур знайшов процесуальні порушення, і провадження закрили."),
    rv("ДП", "Дмитро П.", "Адмінправопорушення", "4 місяці тому", "Постанову скасували в суді. Професійно, швидко і без зайвих слів. Рекомендую."),
]


def link(slug, text):
    return '<a href="%s.html">%s</a>' % (slug, text)


HOME_FAQ = [
    faq("Скільки коштує консультація адвоката?", "Перша консультація безкоштовна. Вартість подальшої роботи залежить від категорії справи та обсягу документів — точну ціну я називаю до початку роботи і фіксую в договорі."),
    faq("Чи можна отримати консультацію онлайн?", "Так. Консультую в Telegram, Viber, WhatsApp або відеозв’язком. Документи можна надіслати фото чи скан-копією."),
    faq("З якими справами ви працюєте?",
        "%s, %s, %s, спори %s, %s, %s та %s, а також транспортне право: %s, %s, %s і %s." % (
            link("trudovi-spory", "Трудові спори"), link("korupciyni-spravy", "корупційні справи"),
            link("ekonomichni-zlochyny", "економічні та фінансові злочини"), link("advokat-po-tck", "з ТЦК та СП"),
            link("krytychnist-pidpryiemstv", "критичність підприємств"), link("administratyvni-pravoporushennya", "адміністративна практика"),
            link("shtrafy-za-perevantazhennya", "штрафи за перевантаження"), link("licenziya-na-perevezennya", "ліцензії на перевезення"),
            link("chip-karta-vodiya", "чіп-карта водія"), link("kod-95", "Код 95"), link("kod-eori", "код EORI"))),
    faq("Ви працюєте з підприємствами чи лише з фізичними особами?", "З обома. Перевізникам і компаніям допомагаю з ліцензуванням, перевірками та захистом посадових осіб; фізичним особам — у кримінальних, військових та адміністративних справах."),
    faq("Хто буде вести мою справу?", "Я особисто. Це індивідуальна адвокатська практика: справи не передаються помічникам чи іншим юристам."),
    faq("Чи гарантуєте ви результат?", "Жоден чесний адвокат не гарантує рішення суду. Я гарантую професійний захист, прозорість кожного кроку і чесну оцінку перспектив."),
]

GENERIC_TOPICS = ["Безкоштовна консультація", "Зворотний дзвінок", "Захист у суді", "Інше питання"]


def uniq(seq):
    out = []
    for x in seq:
        if x not in out:
            out.append(x)
    return out


def related_for(svc, services):
    """Спочатку послуги тієї ж категорії, потім сусідні за списком; разом 3."""
    slugs = [s["slug"] for s in services]
    same = [s for s in services if s["category"] == svc["category"] and s["slug"] != svc["slug"]]
    idx = slugs.index(svc["slug"])
    rest = [services[(idx + k) % len(services)] for k in range(1, len(services))]
    rest = [s for s in rest if s["slug"] not in [x["slug"] for x in same]]
    return (same + rest)[:3]


LANGS = [
    {"code": "uk", "label": "UA", "name": "Українська", "dir": ""},
    {"code": "ru", "label": "RU", "name": "Русский", "dir": "ru/"},
]

# Тексти, що відрізняються для мов, але не перекладаються за словником
SITE_I18N = {
    "ru": {"name": "Сергей Мазур", "address": "г. Киев, ул. Симона Петлюры, 8", "hours": "Пн–Пт 9:00–19:00"},
}


def page_langs(lang, filename):
    """Посилання на цю ж сторінку в усіх мовах (відносно поточної папки)."""
    cur = next(l for l in LANGS if l["code"] == lang)
    out = []
    for l in LANGS:
        if l["code"] == lang:
            href = filename
        elif cur["dir"]:            # з ru/ на корінь
            href = "../" + l["dir"] + filename
        else:                       # з кореня в ru/
            href = l["dir"] + filename
        out.append(dict(l, href=href, active=l["code"] == lang))
    return out


def main():
    written = []
    total_missing = 0
    for L in LANGS:
        lang = L["code"]
        tr = Translator(lang)
        env = Environment(loader=FileSystemLoader(os.path.join(HERE, "templates")),
                          undefined=StrictUndefined, trim_blocks=False, lstrip_blocks=False,
                          keep_trailing_newline=True)
        env.globals["_"] = tr.gettext
        out_dir = os.path.join(OUT, L["dir"])
        os.makedirs(out_dir, exist_ok=True)

        site = dict(SITE, **SITE_I18N.get(lang, {}))
        services = tr.obj(SERVICES)
        svc_by_slug = {s["slug"]: s for s in services}
        cases = tr.obj(CASES)
        generic = tr.obj(GENERIC_TOPICS)
        base = {"root": "../" if L["dir"] else "", "html_lang": lang, "lang_label": L["label"],
                "svc_by_slug": svc_by_slug, "is_case": False}

        def render(tpl, filename, **page):
            page = dict(base, langs=page_langs(lang, filename), **page)
            html = env.get_template(tpl).render(page=page, services=services, site=site)
            path = os.path.join(out_dir, filename)
            open(path, "w", encoding="utf-8").write(html)
            written.append(path)

        # --- головна
        render("home.j2", "index.html",
               slug="index", is_home=True, home="", body_class="page-home",
               title=tr.text("Адвокат Мазур — захист прав фізичних осіб і бізнесу"),
               description=tr.text("Адвокат Сергій Мазур: трудові спори, кримінальні, адміністративні та транспортні справи, спори з ТЦК, ліцензії перевізників. Особистий захист від першої консультації до рішення суду."),
               topics=uniq([s["nav"] for s in services] + generic + [tr.text("Інше")]),
               reviews=tr.obj(HOME_REVIEWS), faq=tr.obj(HOME_FAQ))

        # --- послуги
        shared_faq = tr.obj(SHARED_FAQ_FIRST)
        steps_head = tr.obj(DEFAULT_STEPS_HEAD)
        other_q = tr.text("Інше питання")
        for svc in services:
            svc = dict(svc)
            svc["steps"] = svc.get("steps") or (steps_head + [svc["step_last"]])
            svc["faq"] = [shared_faq] + svc["faq"]
            topics = uniq([x["topic"] for x in svc["situations"] if x["topic"] != other_q]
                          + [t["topic"] for t in svc["tabs"] if t["topic"] != other_q]
                          + generic)
            render("service.j2", svc["slug"] + ".html",
                   slug=svc["slug"], is_home=False, home="index.html", body_class="page-service",
                   title=svc["title"], description=svc["description"],
                   topics=topics, svc=svc, related=related_for(svc, services),
                   svc_cases=[c for c in cases if c["service"] == svc["slug"]][:3])

        # --- кейси
        render("cases.j2", "keysy.html",
               slug="keysy", is_home=False, home="index.html", body_class="page-cases",
               title=tr.text("Кейси адвоката Сергія Мазура — приклади справ з практики"),
               description=tr.text("Приклади справ з практики адвоката Сергія Мазура: трудові спори, ТЦК, корупційні справи, штрафи Укртрансбезпеки, ліцензії перевізників."),
               topics=uniq(generic + [s["nav"] for s in services]), cases=cases)
        for c in cases:
            more = [x for x in cases if x["slug"] != c["slug"]]
            more = sorted(more, key=lambda x: x["service"] != c["service"])[:3]
            render("case.j2", "keys-%s.html" % c["slug"],
                   slug="keys-" + c["slug"], is_home=False, home="index.html", body_class="page-case", is_case=True,
                   title="%s %s | %s" % (tr.text("Кейс:"), c["title"], tr.text("Адвокат Сергій Мазур")),
                   description=c["lead"][:180],
                   topics=uniq(generic + [svc_by_slug[c["service"]]["nav"]]), case=c, more_cases=more)

        n = tr.report()
        total_missing += n
        if n:
            print("! %s: неперекладених рядків — %d (див. _build/i18n/missing_%s.tsv)" % (lang, n, lang))

    for p in written:
        print("✓", os.path.relpath(p, OUT))
    return total_missing


if __name__ == "__main__":
    main()
