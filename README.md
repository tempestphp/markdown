TODO:

- `[](*)` support
- Table support
- {} class support
- heading IDs
- ~~ strikethrough ~~
- Task lists - [ ]

```
+---------------+---------------------------+----------+------+-----+----------+----------+--------+
| benchmark     | subject                   | set      | revs | its | mem_peak | mode     | rstdev |
+---------------+---------------------------+----------+------+-----+----------+----------+--------+
| MarkdownBench | benchTempest              | 01-small | 3    | 5   | 3.676mb  | 0.148ms  | ±1.18% |
| MarkdownBench | benchTempest              | 02-large | 3    | 5   | 5.860mb  | 5.667ms  | ±0.61% |
| MarkdownBench | benchTempestWithHighlight | 01-small | 3    | 5   | 3.903mb  | 0.803ms  | ±2.82% |
| MarkdownBench | benchTempestWithHighlight | 02-large | 3    | 5   | 6.637mb  | 21.194ms | ±0.63% |
| MarkdownBench | benchLeague               | 01-small | 3    | 5   | 4.091mb  | 0.717ms  | ±1.03% |
| MarkdownBench | benchLeague               | 02-large | 3    | 5   | 21.101mb | 49.975ms | ±0.75% |
| MarkdownBench | benchMichelf              | 01-small | 3    | 5   | 3.225mb  | 0.337ms  | ±2.50% |
| MarkdownBench | benchMichelf              | 02-large | 3    | 5   | 7.330mb  | 21.396ms | ±0.94% |
| MarkdownBench | benchErusev               | 01-small | 3    | 5   | 3.030mb  | 0.198ms  | ±2.43% |
| MarkdownBench | benchErusev               | 02-large | 3    | 5   | 8.472mb  | 14.194ms | ±0.62% |
+---------------+---------------------------+----------+------+-----+----------+----------+--------+
```