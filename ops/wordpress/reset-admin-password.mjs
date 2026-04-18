import { createHash } from "node:crypto";

async function main() {
  let mysql;

  try {
    mysql = await import("mysql2/promise");
  } catch {
    throw new Error(
      "mysql2 is not installed. Run `npm install mysql2 --no-save --legacy-peer-deps` before using this script."
    );
  }

  const connectionUrl = process.env.MARIADB_PUBLIC_URL?.trim();
  const username = process.env.WP_ADMIN_USERNAME?.trim();
  const password = process.env.WP_ADMIN_PASSWORD?.trim();

  if (!connectionUrl) {
    throw new Error("MARIADB_PUBLIC_URL is required.");
  }

  if (!username) {
    throw new Error("WP_ADMIN_USERNAME is required.");
  }

  if (!password) {
    throw new Error("WP_ADMIN_PASSWORD is required.");
  }

  const hash = createHash("md5").update(password).digest("hex");
  const connection = await mysql.createConnection(connectionUrl);

  try {
    const [result] = await connection.execute(
      "UPDATE wp_users SET user_pass = ? WHERE user_login = ?",
      [hash, username]
    );

    const affectedRows =
      typeof result === "object" && result !== null && "affectedRows" in result
        ? result.affectedRows
        : 0;

    if (!affectedRows) {
      throw new Error(`No WordPress user found for "${username}".`);
    }

    console.log(`Password reset succeeded for ${username}.`);
  } finally {
    await connection.end();
  }
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : String(error));
  process.exitCode = 1;
});
