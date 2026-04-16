import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";
import { SITE_CONTENT } from "../../src/data/site-content";

const outputDir = path.resolve(process.cwd(), "ops/wordpress/homepage-seeds");

async function main() {
  await mkdir(outputDir, { recursive: true });

  for (const locale of Object.keys(SITE_CONTENT) as Array<keyof typeof SITE_CONTENT>) {
    const outputPath = path.join(outputDir, `homepage-${locale}.json`);
    const payload = `${JSON.stringify(SITE_CONTENT[locale], null, 2)}\n`;
    await writeFile(outputPath, payload, "utf8");
    console.log(`Wrote ${path.relative(process.cwd(), outputPath)}`);
  }
}

main().catch((error) => {
  console.error("Failed to export homepage seeds:", error);
  process.exitCode = 1;
});
