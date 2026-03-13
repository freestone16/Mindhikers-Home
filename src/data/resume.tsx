import type { ComponentType, ReactNode } from "react";

type Skill = {
  name: string;
  icon?: ComponentType<{ className?: string }>;
};

type SocialLink = {
  name: string;
  url: string;
  icon?: ComponentType<{ className?: string }>;
  navbar: boolean;
};

type WorkItem = {
  company: string;
  href: string;
  badges: string[];
  location: string;
  title: string;
  logoUrl: string;
  start: string;
  end?: string;
  description: string;
};

type EducationItem = {
  school: string;
  href: string;
  degree: string;
  logoUrl: string;
  start: string;
  end: string;
};

type ProjectItem = {
  title: string;
  href: string;
  dates: string;
  active: boolean;
  description: string;
  technologies: string[];
  links: Array<{
    type: string;
    href: string;
    icon: ReactNode;
  }>;
  image: string;
  video: string;
};

type HackathonItem = {
  title: string;
  dates: string;
  location: string;
  description: string;
  image: string;
  mlh: string;
  links: Array<{
    title: string;
    icon: ReactNode;
    href: string;
  }>;
};

export const DATA = {
  name: "心行者 Mindhikers",
  initials: "MH",
  url: "https://www.mindhikers.com",
  location: "",
  locationLink: "",
  description:
    "心行者 Mindhikers 是一个双语品牌主页，用来承载内容、方法、产品实验与创作者工具入口。",
  summary: "",
  avatarUrl: "/MindHikers.png",
  skills: [] as Skill[],
  navbar: [] as Array<{
    href: string;
    icon: ComponentType<{ className?: string }>;
    label: string;
  }>,
  contact: {
    email: "",
    tel: "",
    social: {} as Record<string, SocialLink>,
  },
  work: [] as WorkItem[],
  education: [] as EducationItem[],
  projects: [] as ProjectItem[],
  hackathons: [] as HackathonItem[],
};
