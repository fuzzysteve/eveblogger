--
-- PostgreSQL database dump
--


-- Dumped from database version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: category_id(character varying); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.category_id(categoryname character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
retval bigint;
Begin
Insert into categories (category) values (lower(categoryname)) returning category_id into retval;
return retval;
exception
when unique_violation then
select category_id into retval from categories where category=lower(categoryname);
return retval;
END
$$;


--
-- Name: blog_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blog_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: blog_entries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blog_entries (
    id integer DEFAULT nextval('public.blog_entries_id_seq'::regclass) NOT NULL,
    title text NOT NULL,
    guid character varying(510) NOT NULL,
    link text NOT NULL,
    "pubDate" timestamp with time zone NOT NULL,
    description text NOT NULL,
    content text NOT NULL,
    date_entered timestamp with time zone NOT NULL,
    num_comments integer DEFAULT 0 NOT NULL,
    author character varying(500) DEFAULT ''::character varying NOT NULL,
    blog integer NOT NULL,
    authoremail character varying(255),
    indexed integer DEFAULT 0
);


--
-- Name: blogs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blogs_real; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blogs_real (
    id integer DEFAULT nextval('public.blogs_id_seq'::regclass) NOT NULL,
    name character varying(200) NOT NULL,
    feedurl character varying(510) NOT NULL,
    owner character varying(200) NOT NULL,
    lastupdate timestamp with time zone NOT NULL,
    description character varying(510) DEFAULT NULL::character varying,
    url character varying(255),
    approved integer DEFAULT 0
);


--
-- Name: blogs; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.blogs AS
 SELECT id,
    name,
    feedurl,
    owner,
    lastupdate,
    description,
    url,
    approved
   FROM public.blogs_real
  WHERE (approved > 0);


--
-- Name: categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categories (
    category_id integer NOT NULL,
    category character varying(200)
);


--
-- Name: categories_category_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categories_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categories_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categories_category_id_seq OWNED BY public.categories.category_id;


--
-- Name: post_category; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_category (
    post_id integer,
    category_id integer
);


--
-- Name: categories category_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories ALTER COLUMN category_id SET DEFAULT nextval('public.categories_category_id_seq'::regclass);


--
-- Name: blog_entries blog_entries_guid_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_entries
    ADD CONSTRAINT blog_entries_guid_key UNIQUE (guid);


--
-- Name: blog_entries blog_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_entries
    ADD CONSTRAINT blog_entries_pkey PRIMARY KEY (id);


--
-- Name: blogs_real blogs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogs_real
    ADD CONSTRAINT blogs_pkey PRIMARY KEY (id);


--
-- Name: categories cattext; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT cattext UNIQUE (category);


--
-- Name: bentries_pubdate; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bentries_pubdate ON public.blog_entries USING btree ("pubDate");


--
-- Name: blog_entries_blog_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blog_entries_blog_idx ON public.blog_entries USING btree (blog);


--
-- Name: blog_pub_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blog_pub_id ON public.blog_entries USING btree (blog, "pubDate", id);


--
-- Name: blog_entries blog; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_entries
    ADD CONSTRAINT blog FOREIGN KEY (blog) REFERENCES public.blogs_real(id) DEFERRABLE INITIALLY DEFERRED;


--
-- PostgreSQL database dump complete
--


