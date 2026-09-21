// App-wide runtime config, sourced from the build-time environment.
//
// The trading region is shown in the header ("EU wholesale", etc.) and is set
// per deployment via the environment (see .env / .env.example).
export const REGION = import.meta.env.REGION ?? '';
