--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: blog_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE blog_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blog_entries_id_seq OWNER TO postgres;

--
-- Name: blog_entries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('blog_entries_id_seq', 2397, true);


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: blog_entries; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE blog_entries (
    id integer DEFAULT nextval('blog_entries_id_seq'::regclass) NOT NULL,
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


ALTER TABLE public.blog_entries OWNER TO postgres;

--
-- Name: blogs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE blogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blogs_id_seq OWNER TO postgres;

--
-- Name: blogs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('blogs_id_seq', 48, true);


--
-- Name: blogs_real; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE blogs_real (
    id integer DEFAULT nextval('blogs_id_seq'::regclass) NOT NULL,
    name character varying(200) NOT NULL,
    feedurl character varying(510) NOT NULL,
    owner character varying(200) NOT NULL,
    lastupdate timestamp with time zone NOT NULL,
    description character varying(510) DEFAULT NULL::character varying,
    url character varying(255),
    approved integer DEFAULT 0
);


ALTER TABLE public.blogs_real OWNER TO postgres;

--
-- Name: blogs; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW blogs AS
    SELECT blogs_real.id, blogs_real.name, blogs_real.feedurl, blogs_real.owner, blogs_real.lastupdate, blogs_real.description, blogs_real.url, blogs_real.approved FROM blogs_real WHERE (blogs_real.approved = 1);


ALTER TABLE public.blogs OWNER TO postgres;
