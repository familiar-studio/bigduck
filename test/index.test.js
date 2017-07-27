import test from "ava";
import Nuxt from "nuxt";
import { resolve } from "path";

// We keep the nuxt and server instance
// So we can close them at the end of the test
let nuxt = null;
let server = null;

// Init Nuxt.js and create a server listening on localhost:4000
test.before("Init Nuxt.js", async t => {
  const rootDir = resolve(__dirname, "..");
  let config = {};
  try {
    config = require(resolve(rootDir, "nuxt.config.js"));
  } catch (e) {}
  config.rootDir = rootDir; // project folder
  config.dev = false; // production build
  nuxt = new Nuxt(config);
  await nuxt.build();
  server = new nuxt.Server(nuxt);
  server.listen(4000, "localhost");
});

// Example of testing only generated html
test("Route / exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/", context);
  t.true(html.includes("Smart communications for nonprofits"));
});

// Example of testing via dom checking
test("Route / exits and render HTML with CSS applied", async t => {
  const window = await nuxt.renderAndGetWindow("http://localhost:4000/");
  const element = window.document.querySelector(".tagline");
  t.not(element, null);
  t.is(element.textContent, "Smart communications for nonprofits");
  // t.is(element.className, "red");
  // t.is(window.getComputedStyle(element).color, "red");
});

test("Route /services exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/services", context);
  t.true(html.includes("block-overlap"));
  t.true(html.includes("testimonial"));
});

test("Route /work exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/work", context);
  t.true(html.includes("block-work-featured"));
  t.true(html.includes("bg-img"));
});

test("Route /insights exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/insights", context);
  t.true(html.includes("block-overlap"));
  t.true(html.includes("featured-image"));
});

test("Route /about exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/about", context);
  t.true(html.includes("about"));
  t.true(html.includes("clients"));
});

test("Route /contact exits and render HTML", async t => {
  let context = {};
  const { html } = await nuxt.renderRoute("/contact", context);
  t.true(html.includes("form-light"));
});

// Close server and ask nuxt to stop listening to file changes
test.after("Closing server and nuxt.js", t => {
  server.close();
  nuxt.close();
});
