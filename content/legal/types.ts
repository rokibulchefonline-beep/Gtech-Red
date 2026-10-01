export type LegalDoc = {
  title: string;
  intro: string;
  updated: string;
  sections: { h: string; p?: string[]; ul?: string[]; after?: string[] }[];
};
