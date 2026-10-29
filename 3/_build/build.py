# -*- coding: utf-8 -*-
"""
Збірка статичних сторінок дизайну 3.

    python3 3/_build/build.py

Шаблони: _build/templates/*.j2, контент послуг: _build/data_services.py.
Результат: 3/index.html + 3/<slug>.html для кожної послуги.
Сторінки згенеровані — правки контенту вносимо в дані/шаблони і перезбираємо.
"""
import os
import sys

from jinja2 import Environment, FileSystemLoader, StrictUndefined

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.dirname(HERE)
sys.path.insert(0, HERE)
from data_services import SERVICES, DEFAULT_STEPS_HEAD, SHARED_FAQ_FIRST, rv, faq  # noqa: E402

SITE = {
    # контакти — з чинного сайту advokatmazur.com.ua
    "name": "Сергій Мазур",
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


def related_for(svc):
    """Спочатку послуги тієї ж категорії, потім сусідні за списком; разом 3."""
    slugs = [s["slug"] for s in SERVICES]
    same = [s for s in SERVICES if s["category"] == svc["category"] and s["slug"] != svc["slug"]]
    idx = slugs.index(svc["slug"])
    rest = [SERVICES[(idx + k) % len(SERVICES)] for k in range(1, len(SERVICES))]
    rest = [s for s in rest if s["slug"] not in [x["slug"] for x in same]]
    return (same + rest)[:3]


def main():
    env = Environment(loader=FileSystemLoader(os.path.join(HERE, "templates")),
                      undefined=StrictUndefined, trim_blocks=False, lstrip_blocks=False,
                      keep_trailing_newline=True)

    written = []

    # --- головна
    home_page = {
        "slug": "index", "is_home": True, "home": "", "body_class": "page-home",
        "title": "Адвокат Мазур — захист прав фізичних осіб і бізнесу",
        "description": "Адвокат Мазур: кримінальні, адміністративні та транспортні справи, спори з ТЦК, ліцензії перевізників. Особистий захист від першої консультації до рішення суду.",
        "topics": uniq([s["nav"] for s in SERVICES] + GENERIC_TOPICS + ["Інше"]),
        "reviews": HOME_REVIEWS, "faq": HOME_FAQ,
    }
    html = env.get_template("home.j2").render(page=home_page, services=SERVICES, site=SITE)
    path = os.path.join(OUT, "index.html")
    open(path, "w", encoding="utf-8").write(html)
    written.append(path)

    # --- послуги
    for svc in SERVICES:
        svc = dict(svc)
        svc["steps"] = svc.get("steps") or (DEFAULT_STEPS_HEAD + [svc["step_last"]])
        svc["faq"] = [SHARED_FAQ_FIRST] + svc["faq"]
        topics = uniq([x["topic"] for x in svc["situations"] if x["topic"] != "Інше питання"]
                      + [t["topic"] for t in svc["tabs"] if t["topic"] != "Інше питання"]
                      + GENERIC_TOPICS)
        page = {
            "slug": svc["slug"], "is_home": False, "home": "index.html", "body_class": "page-service",
            "title": svc["title"], "description": svc["description"],
            "topics": topics, "svc": svc, "related": related_for(svc),
        }
        html = env.get_template("service.j2").render(page=page, services=SERVICES, site=SITE)
        path = os.path.join(OUT, svc["slug"] + ".html")
        open(path, "w", encoding="utf-8").write(html)
        written.append(path)

    for p in written:
        print("✓", os.path.relpath(p, OUT))


if __name__ == "__main__":
    main()
