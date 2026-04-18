import { NextResponse } from "next/server";

export function GET() {
  return NextResponse.json({
    ok: true,
    service: "mindhikers-homepage",
    version: "phase-closure",
    timestamp: new Date().toISOString(),
  });
}
