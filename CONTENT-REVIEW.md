# Content review — files kept, but nothing links to them

The cleanup pass deleted ~27 MB of dead theme code (see README). These
items are also unreferenced, but they are **your material, not theme junk**,
so nothing here was touched. Decide what to keep.

## Directories

| Path | Size | Notes |
|------|------|-------|
| `Photos/` | 15 MB | Photographs of gatherings. Probably the originals behind the photo gallery — the gallery itself serves resized copies from `img/`. |
| `vedios/` | 1.6 MB | Video assets (folder name is a typo for "videos"). |
| `articles/` | 72 KB | Written articles. Not linked from any current page. |

## Unreferenced images in `img/`

Nothing on the site links these 14 files:

```
Donations.jpg   Parayanam.jpg   -old.jpg        contact.jpg
events/         g_1.jpg         img_1.png       img_2.png
img_3.png       img_4.png       img_5.png       logo_n_1.png
logo_s_2.jpg    registration.jpg  signin.jpg
```

`registration.jpg` and `signin.jpg` were the headers for the old static
`registration.html` / `signin.html` pages, which are gone — the real portal
pages replaced them. The `img_1`–`img_5` set and `logo_n_1.png` look like
unused variants.

## Suggested next step

The Gallery admin feature is still on the backlog. When it is built it will
want a real media library, and `Photos/` plus `vedios/` are the obvious
source. Keeping them until then costs 17 MB and loses nothing.
